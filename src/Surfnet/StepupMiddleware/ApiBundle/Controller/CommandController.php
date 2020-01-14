<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\StepupMiddleware\ApiBundle\Controller;

use Psr\Log\LoggerInterface;
use Surfnet\Stepup\Configuration\Value\InstitutionRole;
use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\Institution;
use Surfnet\StepupMiddleware\ApiBundle\Authorization\Service\InstitutionAuthorizationService;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Service\IdentityService;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Service\WhitelistService;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\Command;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\Metadata;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\RaExecutable;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\SelfServiceExecutable;
use Surfnet\StepupMiddleware\CommandHandlingBundle\EventSourcing\MetadataEnricher;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Exception\ForbiddenException;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\CreateIdentityCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command\UpdateIdentityCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Pipeline\TransactionAwarePipeline;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommandController extends Controller
{
    /**
     * @var WhitelistService
     */
    private $whitelistService;

    /**
     * @var IdentityService
     */
    private $identityService;

    /**
     * @var TransactionAwarePipeline
     */
    private $pipeline;

    /**
     * @var MetadataEnricher
     */
    private $metadataEnricher;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var InstitutionAuthorizationService
     */
    private $institutionAuthorizationService;

    public function __construct(
        TransactionAwarePipeline $pipeline,
        WhitelistService $whitelistService,
        IdentityService $identityService,
        MetadataEnricher $enricher,
        AuthorizationChecker $authorizationChecker,
        LoggerInterface $logger,
        InstitutionAuthorizationService $institutionAuthorizationService
    ) {
        $this->pipeline = $pipeline;
        $this->whitelistService = $whitelistService;
        $this->identityService = $identityService;
        $this->authorizationChecker = $authorizationChecker;
        $this->metadataEnricher = $enricher;
        $this->logger = $logger;
        $this->institutionAuthorizationService = $institutionAuthorizationService;
    }

    public function handleAction(Command $command, Metadata $metadata, Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA', 'ROLE_SS']);

        $logger = $this->logger;
        $logger->notice(sprintf('Received request to process Command "%s"', $command));

        $this->metadataEnricher->setMetadata($metadata);

        if ($this->authorizationChecker->isGranted('ROLE_MANAGEMENT')) {
            $logger->notice('Command sent through Management API, not enforcing Whitelist');
        } else {
            $logger->notice('Ensuring that the actor institution is on the whitelist, or the actor is SRAA');

            $institution = $this->resolveInstitution($command, $metadata);
            $this->assertInstitutionIsWhitelisted($institution, $metadata->actorId);


            $logger->notice('Ensuring that the actor is allowed to execute a command based on the fine grained authorization configuration');

            $this->assertSelfServiceCommandMayBeExecutedOnBehalfOf($metadata->actorId, $command);
            $this->assertRaCommandMayBeExecutedOnBehalfOf($metadata->actorId, $metadata->actorInstitution, $command);

            $logger->notice('Command authorization succeeded');
        }

        try {
            $command = $this->pipeline->process($command);
        } catch (ForbiddenException $e) {
            throw new AccessDeniedHttpException(
                sprintf('Processing of command "%s" is forbidden for this client', $command),
                $e
            );
        }

        $serverName = $request->server->get('SERVER_NAME') ?: $request->server->get('SERVER_ADDR');
        $response = new JsonResponse(['command' => $command->UUID, 'processed_by' => $serverName]);

        $logger->notice(sprintf('Command "%s" has been successfully processed', $command));

        return $response;
    }

    /**
     * @param Command  $command
     * @param Metadata $metadata
     * @return string
     */
    private function resolveInstitution(Command $command, Metadata $metadata)
    {
        if ($metadata->actorInstitution) {
            return $metadata->actorInstitution;
        }

        // the createIdentityCommand is used to create an Identity for a new user,
        // the updateIdentityCommand is used to update name or email of an identity
        // Both are only sent by the SS when the Identity is not logged in yet,
        // thus there is not Metadata::actorInstitution,
        if ($command instanceof CreateIdentityCommand || $command instanceof UpdateIdentityCommand) {
            return $command->institution;
        }

        // conservative, if we cannot determine an institution, deny processing.
        throw new AccessDeniedHttpException(
            'Cannot reliably determine the institution of the actor, denying processing of command'
        );
    }

    /**
     * @param string      $institution
     * @param string|null $actorId
     */
    private function assertInstitutionIsWhitelisted($institution, $actorId)
    {
        if ($this->whitelistService->isWhitelisted($institution)) {
            return;
        }

        if (!$actorId) {
            throw new AccessDeniedHttpException(sprintf(
                'Institution "%s" is not on the whitelist and no actor is found, processing of command denied',
                $institution
            ));
        }

        if ($this->isSraa($actorId)) {
            return;
        }

        throw new AccessDeniedHttpException(sprintf(
            'Institution "%s" is not on the whitelist and actor "%s" is not an SRAA, processing of command denied',
            $institution,
            $actorId
        ));
    }

    /**
     * @param string $actorId
     * @param Command $command
     */
    private function assertSelfServiceCommandMayBeExecutedOnBehalfOf($actorId, Command $command)
    {
        // Assert self service command could be executed
        if ($command instanceof SelfServiceExecutable) {
            $this->logger->notice('Asserting a SelfService command');

            // the createIdentityCommand is used to create an Identity for a new user,
            // the updateIdentityCommand is used to update name or email of an identity
            // Both are only sent by the SS when the Identity is not logged in yet,
            // thus there is not Metadata::actorInstitution,
            if ($command instanceof CreateIdentityCommand || $command instanceof UpdateIdentityCommand) {
                return;
            }

            // If the actor is SRAA all actions should be allowed
            if ($this->isSraa($actorId)) {
                return;
            }

            // Validate if the actor is the user
            if ($command->getIdentityId() !== $actorId) {
                throw new AccessDeniedHttpException(sprintf(
                    'The actor "%s" is not allowed to act on behalf of identity "%s" processing of command denied',
                    $actorId,
                    $command->getIdentityId()
                ));
            }

            return;
        }
    }

    /**
     * @param string $actorId
     * @param string $actorInstitution
     * @param Command $command
     */
    private function assertRaCommandMayBeExecutedOnBehalfOf($actorId, $actorInstitution, Command $command)
    {
        // Assert RAA specific authorizations
        if ($command instanceof RaExecutable) {
            $this->logger->notice('Asserting a RA command');

            // If the actor is SRAA all actions should be allowed
            if ($this->isSraa($actorId)) {
                return;
            }

            $raInstitution = $command->getRaInstitution();
            if (is_null($raInstitution)) {
                $raInstitution = $actorInstitution;
            }

            $authorizationContext = $this->institutionAuthorizationService->buildInstitutionAuthorizationContext(
                new IdentityId($actorId),
                InstitutionRole::useRaa()
            );

            if (!$authorizationContext->getInstitutions()->contains(new Institution($raInstitution))) {
                throw new AccessDeniedHttpException(sprintf(
                    'The actor "%s" is not allowed to act on behalf of institution  "%s" processing of command denied',
                    $actorId,
                    $raInstitution
                ));
            }
        }
    }

    /**
     * @param string $actorId
     * @return bool
     */
    private function isSraa($actorId)
    {
        $registrationAuthorityCredentials = $this->identityService->findRegistrationAuthorityCredentialsOf($actorId);
        if (!$registrationAuthorityCredentials) {
            return false;
        }

        if (!$registrationAuthorityCredentials->isSraa()) {
            return false;
        }

        return true;
    }
}

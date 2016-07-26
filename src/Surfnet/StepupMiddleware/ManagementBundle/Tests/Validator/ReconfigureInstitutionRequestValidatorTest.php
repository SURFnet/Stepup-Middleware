<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace Surfnet\StepupMiddleware\ManagementBundle\Tests\Validator;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\StepupMiddleware\ApiBundle\Configuration\Entity\ConfiguredInstitution;
use Surfnet\StepupMiddleware\ApiBundle\Configuration\Service\ConfiguredInstitutionService;
use Surfnet\StepupMiddleware\ManagementBundle\Validator\Constraints\ValidReconfigureInstitutionsRequest;
use Surfnet\StepupMiddleware\ManagementBundle\Validator\ReconfigureInstitutionRequestValidator;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ReconfigureInstitutionRequestValidatorTest extends TestCase
{
    public function invalidReconfigureInstitutionRequests()
    {
        $dataSet = [];

        foreach (glob(__DIR__ . '/Fixtures/invalid_reconfigure_institution_request/*.php') as $invalidConfiguration) {
            $fixture = include $invalidConfiguration;
            $dataSet[basename($invalidConfiguration)] = [
                $fixture['reconfigureInstitutionRequest'],
                $fixture['expectedPropertyPath'],
                $fixture['expectErrorMessageToContain']
            ];
        };

        return $dataSet;
    }

    /**
     * @test
     * @group validator
     * @dataProvider invalidReconfigureInstitutionRequests
     * @param array $reconfigureRequest
     * @param string $expectedPropertyPath
     * @param string $expectErrorMessageToContain
     */
    public function it_rejects_invalid_configuration(
        $reconfigureRequest,
        $expectedPropertyPath,
        $expectErrorMessageToContain
    ) {
        $existingInstitution              = new ConfiguredInstitution;
        $existingInstitution->institution = 'surfnet.nl';

        $anotherExistingInstitution              = new ConfiguredInstitution;
        $anotherExistingInstitution->institution = 'another-organisation.test';

        $configuredInstitutionServiceMock = Mockery::mock(ConfiguredInstitutionService::class);
        $configuredInstitutionServiceMock
            ->shouldReceive('getAll')
            ->andReturn([
                $existingInstitution,
                $anotherExistingInstitution
            ]);

        $builder = Mockery::mock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
        $builder->shouldReceive('addViolation')->with()->once();
        $builder->shouldReceive('atPath')->with(self::spy($actualPropertyPath))->once();

        $context = Mockery::mock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $context->shouldReceive('buildViolation')->with(self::spy($actualErrorMessage))->once()->andReturn($builder);


        $validator = new ReconfigureInstitutionRequestValidator($configuredInstitutionServiceMock);
        $validator->initialize($context);
        $validator->validate($reconfigureRequest, new ValidReconfigureInstitutionsRequest);

        // PHPUnit assertions are more informative than Mockery's method-should-be-called-1-times-but-called-0-times.
        $this->assertEquals(
            $expectedPropertyPath,
            $actualPropertyPath,
            sprintf('Actual path to erroneous property does not match expected path (%s)', $actualErrorMessage)
        );
        $this->assertContains(
            $expectErrorMessageToContain,
            $actualErrorMessage,
            sprintf(
                'The error message (%s) does not contain the expected message (%s)',
                $actualErrorMessage,
                $expectErrorMessageToContain
            )
        );
    }

    /**
     * @test
     * @group validator
     */
    public function reconfigure_institution_request_cannot_contain_institutions_that_do_not_exist()
    {
        $existingInstitutions = [];
        $nonExistentInstitution = 'non-existing.organisation.test';
        $expectedErrorMessage = 'Cannot reconfigure non-existent institution';

        $invalidRequest = [$nonExistentInstitution => []];

        $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
        $builder->shouldReceive('addViolation')->with()->once();

        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')->once()->with(self::spy($errorMessage))->andReturn($builder);

        $configuredInstitutionServiceMock = Mockery::mock(ConfiguredInstitutionService::class);
        $configuredInstitutionServiceMock
            ->shouldReceive('getAll')
            ->andReturn($existingInstitutions);

        $validator = new ReconfigureInstitutionRequestValidator($configuredInstitutionServiceMock);
        $validator->initialize($context);
        
        $validator->validate($invalidRequest, new ValidReconfigureInstitutionsRequest);

        $this->assertContains($expectedErrorMessage, $errorMessage);
    }

    /**
     * @test
     * @group validator
     */
    public function valid_reconfigure_institution_requests_do_not_cause_any_violations()
    {
        $institution = 'surfnet.nl';
        $validRequest = [
            $institution => [
                'use_ra_locations'             => true,
                'show_raa_contact_information' => true,
            ],
        ];

        $existingInstitution = new ConfiguredInstitution;
        $existingInstitution->institution = $institution;

        $configuredInstitutionServiceMock = Mockery::mock(ConfiguredInstitutionService::class);
        $configuredInstitutionServiceMock
            ->shouldReceive('getAll')
            ->andReturn([$existingInstitution]);

        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldNotReceive('buildViolation');

        $validator = new ReconfigureInstitutionRequestValidator($configuredInstitutionServiceMock);
        $validator->initialize($context);
        $validator->validate($validRequest, new ValidReconfigureInstitutionsRequest);
    }

    /**
     * @param mixed &$spy
     * @return \Mockery\Matcher\MatcherAbstract
     */
    private static function spy(&$spy)
    {
        return Mockery::on(
            function ($value) use (&$spy) {
                $spy = $value;

                return true;
            }
        );
    }
}

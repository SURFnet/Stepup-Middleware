<?php

/**
 * Copyright 2018 SURFnet bv
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

namespace Surfnet\Stepup\Identity\Event;

use Surfnet\Stepup\DateTime\DateTime;
use Surfnet\Stepup\Identity\AuditLog\Metadata;
use Surfnet\Stepup\Identity\Value\CommonName;
use Surfnet\Stepup\Identity\Value\Email;
use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\Institution;
use Surfnet\Stepup\Identity\Value\U2fKeyHandle;
use Surfnet\Stepup\Identity\Value\SecondFactorId;
use Surfnet\StepupBundle\Value\SecondFactorType;
use Surfnet\StepupMiddleware\CommandHandlingBundle\SensitiveData\Forgettable;
use Surfnet\StepupMiddleware\CommandHandlingBundle\SensitiveData\SensitiveData;

class U2fDevicePossessionProvenAndVerifiedEvent extends IdentityEvent implements Forgettable
{
    /**
     * @var \Surfnet\Stepup\Identity\Value\SecondFactorId
     */
    public $secondFactorId;

    /**
     * @var \Surfnet\Stepup\Identity\Value\U2fKeyHandle
     */
    public $keyHandle;

    /**
     * @var \Surfnet\Stepup\Identity\Value\CommonName
     */
    public $commonName;

    /**
     * @var \Surfnet\Stepup\Identity\Value\Email
     */
    public $email;

    /**
     * @var \Surfnet\Stepup\DateTime\DateTime
     */
    public $registrationRequestedAt;

    /**
     * @var string
     */
    public $registrationCode;

    /**
     * @param IdentityId $identityId
     * @param Institution $identityInstitution
     * @param SecondFactorId $secondFactorId
     * @param U2fKeyHandle $keyHandle
     * @param CommonName $commonName
     * @param Email $email
     * @param DateTime $registrationRequestedAt
     * @param string $registrationCode
     */
    public function __construct(
        IdentityId $identityId,
        Institution $identityInstitution,
        SecondFactorId $secondFactorId,
        U2fKeyHandle $keyHandle,
        CommonName $commonName,
        Email $email,
        DateTime $registrationRequestedAt,
        $registrationCode
    ) {
        parent::__construct($identityId, $identityInstitution);

        $this->secondFactorId = $secondFactorId;
        $this->keyHandle = $keyHandle;
        $this->commonName = $commonName;
        $this->email = $email;
        $this->registrationRequestedAt = $registrationRequestedAt;
        $this->registrationCode = $registrationCode;
    }

    public function getAuditLogMetadata()
    {
        $metadata                         = new Metadata();
        $metadata->identityId             = $this->identityId;
        $metadata->identityInstitution    = $this->identityInstitution;
        $metadata->secondFactorId         = $this->secondFactorId;
        $metadata->secondFactorType       = new SecondFactorType('sms');
        $metadata->secondFactorIdentifier = $this->keyHandle;

        return $metadata;
    }

    public static function deserialize(array $data)
    {
        return new self(
            new IdentityId($data['identity_id']),
            new Institution($data['identity_institution']),
            new SecondFactorId($data['second_factor_id']),
            U2fKeyHandle::unknown(),
            CommonName::unknown(),
            Email::unknown(),
            DateTime::fromString($data['registration_requested_at']),
            (string) $data['registration_code']
        );
    }

    public function serialize()
    {
        return [
            'identity_id'                 => (string) $this->identityId,
            'identity_institution'        => (string) $this->identityInstitution,
            'second_factor_id'            => (string) $this->secondFactorId,
            'registration_requested_at'   => (string) $this->registrationRequestedAt,
            'registration_code'           => $this->registrationCode,
        ];
    }

    public function getSensitiveData()
    {
        return (new SensitiveData)
            ->withCommonName($this->commonName)
            ->withEmail($this->email)
            ->withSecondFactorIdentifier($this->keyHandle, new SecondFactorType('u2f'));
    }

    public function setSensitiveData(SensitiveData $sensitiveData)
    {
        $this->keyHandle   = $sensitiveData->getSecondFactorIdentifier();
        $this->email       = $sensitiveData->getEmail();
        $this->commonName  = $sensitiveData->getCommonName();
    }
}

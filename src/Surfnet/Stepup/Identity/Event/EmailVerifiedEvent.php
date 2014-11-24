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

namespace Surfnet\Stepup\Identity\Event;

use Broadway\Domain\DateTime;
use Broadway\Serializer\SerializableInterface;
use Surfnet\Stepup\Identity\Value\SecondFactorId;

class EmailVerifiedEvent implements SerializableInterface
{
    /**
     * @var SecondFactorId
     */
    public $secondFactorId;

    /**
     * @var DateTime
     */
    public $registrationRequestedAt;

    /**
     * @var string
     */
    public $registrationCode;

    /**
     * @var string
     */
    public $registrationNonce;

    /**
     * @param SecondFactorId $secondFactorId
     * @param DateTime $registrationRequestedAt
     * @param string $registrationCode
     * @param string $registrationNonce
     */
    public function __construct(SecondFactorId $secondFactorId, DateTime $registrationRequestedAt, $registrationCode, $registrationNonce)
    {
        $this->secondFactorId = $secondFactorId;
        $this->registrationRequestedAt = $registrationRequestedAt;
        $this->registrationCode = $registrationCode;
        $this->registrationNonce = $registrationNonce;
    }

    public static function deserialize(array $data)
    {
        return new self(
            new SecondFactorId(
                $data['second_factor_id']
            ),
            DateTime::fromString($data['registration_requested_at']),
            $data['registration_code'],
            $data['registration_nonce']
        );
    }

    public function serialize()
    {
        return [
            'second_factor_id' => (string) $this->secondFactorId,
            'registration_requested_at' => $this->registrationRequestedAt->toString(),
            'registration_code' => $this->registrationCode,
            'registration_nonce' => $this->registrationNonce,
        ];
    }
}
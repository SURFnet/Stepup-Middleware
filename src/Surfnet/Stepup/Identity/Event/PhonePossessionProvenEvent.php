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

use Surfnet\Stepup\Identity\Value\IdentityId;
use Surfnet\Stepup\Identity\Value\PhoneNumber;
use Surfnet\Stepup\Identity\Value\SecondFactorId;

class PhonePossessionProvenEvent extends IdentityEvent
{
    /**
     * The UUID of the second factor that has been proven to be in possession of the registrant.
     *
     * @var SecondFactorId
     */
    public $secondFactorId;

    /**
     * @var PhoneNumber
     */
    public $phoneNumber;

    /**
     * @param IdentityId $identityId
     * @param SecondFactorId $secondFactorId
     * @param PhoneNumber $phoneNumber
     */
    public function __construct(IdentityId $identityId, SecondFactorId $secondFactorId, PhoneNumber $phoneNumber)
    {
        parent::__construct($identityId);

        $this->secondFactorId = $secondFactorId;
        $this->phoneNumber = $phoneNumber;
    }

    public static function deserialize(array $data)
    {
        return new self(
            new IdentityId($data['identity_id']),
            new SecondFactorId($data['second_factor_id']),
            new PhoneNumber($data['phone_number'])
        );
    }

    public function serialize()
    {
        return [
            'identity_id'      => (string) $this->identityId,
            'second_factor_id' => (string) $this->secondFactorId,
            'phone_number'     => (string) $this->phoneNumber
        ];
    }
}
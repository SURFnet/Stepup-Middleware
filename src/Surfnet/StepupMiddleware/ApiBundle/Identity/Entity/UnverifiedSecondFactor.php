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

namespace Surfnet\StepupMiddleware\ApiBundle\Identity\Entity;

use Doctrine\ORM\Mapping as ORM;
use Surfnet\Stepup\DateTime\DateTime;
use Surfnet\StepupMiddleware\ApiBundle\Exception\InvalidArgumentException;

/**
 * @ORM\Entity(
 *     repositoryClass="Surfnet\StepupMiddleware\ApiBundle\Identity\Repository\UnverifiedSecondFactorRepository"
 * )
 */
class UnverifiedSecondFactor implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(length=36)
     *
     * @var string
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Identity", inversedBy="unverifiedSecondFactors")
     *
     * @var Identity
     */
    public $identity;

    /**
     * @ORM\Column(length=16)
     *
     * @var string
     */
    public $type;

    /**
     * The second factor identifier, ie. telephone number, Yubikey public ID, Tiqr ID
     *
     * @ORM\Column(length=255)
     *
     * @var string
     */
    public $secondFactorIdentifier;

    /**
     * @ORM\Column(length=32)
     *
     * @var string
     */
    public $verificationNonce;

    /**
     * @ORM\Column(type="stepup_datetime", nullable=false)
     *
     * @var
     */
    public $verificationNonceValidUntil;

    /**
     * @param Identity $identity
     * @param string   $id
     * @param string   $type
     * @param string   $secondFactorIdentifier
     * @param string   $verificationNonce
     * @param DateTime $verificationNonceValidUntil
     * @return UnverifiedSecondFactor
     */
    public static function addToIdentity(
        Identity $identity,
        $id,
        $type,
        $secondFactorIdentifier,
        $verificationNonce,
        DateTime $verificationNonceValidUntil
    ) {
        if (!is_string($id)) {
            throw InvalidArgumentException::invalidType('string', 'id', $id);
        }

        if (!is_string($type)) {
            throw InvalidArgumentException::invalidType('string', 'type', $type);
        }

        if (!is_string($secondFactorIdentifier)) {
            throw InvalidArgumentException::invalidType('string', 'secondFactorIdentifier', $secondFactorIdentifier);
        }

        if (!is_string($verificationNonce)) {
            throw InvalidArgumentException::invalidType('string', 'verificationNonce', $verificationNonce);
        }

        $secondFactor = new self;
        $secondFactor->identity = $identity;
        $secondFactor->id = $id;
        $secondFactor->type = $type;
        $secondFactor->secondFactorIdentifier = $secondFactorIdentifier;
        $secondFactor->verificationNonce = $verificationNonce;
        $secondFactor->verificationNonceValidUntil = $verificationNonceValidUntil;

        $identity->unverifiedSecondFactors->add($secondFactor);

        return $secondFactor;
    }

    final private function __construct()
    {
    }

    /**
     * @param string $registrationCode
     * @return VerifiedSecondFactor
     */
    public function verifyEmail($registrationCode)
    {
        $identity = $this->identity;

        $this->identity->unverifiedSecondFactors->removeElement($this);
        $this->identity = null;

        return VerifiedSecondFactor::addToIdentity(
            $identity,
            $this->id,
            $this->type,
            $this->secondFactorIdentifier,
            $registrationCode
        );
    }

    public function jsonSerialize()
    {
        return [
            'id'   => $this->id,
            'type' => $this->type,
            'second_factor_identifier' => $this->secondFactorIdentifier
        ];
    }
}

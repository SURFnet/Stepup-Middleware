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
use JsonSerializable;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Value\SecondFactorStatus;

/**
 * A second factor as displayed in the registration authority application. One exists for every second factor,
 * regardless of state. As such, it sports a status property, indicating whether its vetted, revoked etc.
 *
 * @ORM\Entity(repositoryClass="Surfnet\StepupMiddleware\ApiBundle\Identity\Repository\RaSecondFactorRepository")
 * @ORM\Table(
 *      indexes={
 *          @ORM\Index(name="idx_ra_second_factor_second_factor_id", columns={"second_factor_id"}),
 *          @ORM\Index(name="idx_ra_second_factor_identity_id", columns={"identity_id"}),
 *          @ORM\Index(name="idx_ra_second_factor_institution", columns={"institution"}),
 *          @ORM\Index(name="idx_ra_second_factor_name", columns={"name"}, flags={"FULLTEXT"}),
 *          @ORM\Index(name="idx_ra_second_factor_email", columns={"email"}, flags={"FULLTEXT"}),
 *      }
 * )
 */
final class RaSecondFactor implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(length=36)
     *
     * @var string The second factor's ID (UUID).
     */
    public $id;

    /**
     * @ORM\Column(length=16)
     *
     * @var string
     */
    public $type;

    /**
     * @ORM\Column(length=36)
     *
     * @var string The ID of the specific instance of second factor type (ie. phone number, Yubikey public ID).
     */
    public $secondFactorId;

    /**
     * @ORM\Column(type="stepup_second_factor_status")
     *
     * @var SecondFactorStatus
     */
    public $status;

    /**
     * @ORM\Column(length=36)
     *
     * @var string
     */
    public $identityId;

    /**
     * @ORM\Column
     *
     * @var string
     */
    public $institution;

    /**
     * The name of the registrant.
     *
     * @ORM\Column
     *
     * @var string
     */
    public $name;

    /**
     * The e-mail of the registrant.
     *
     * @ORM\Column
     *
     * @var string
     */
    public $email;

    /**
     * @param string $id
     * @param string $type
     * @param string $secondFactorId
     * @param string $identityId
     * @param string $institution
     * @param string $name
     * @param string $email
     */
    public function __construct($id, $type, $secondFactorId, $identityId, $institution, $name, $email)
    {
        $this->id = $id;
        $this->type = $type;
        $this->secondFactorId = $secondFactorId;
        $this->status = SecondFactorStatus::unverified();
        $this->identityId = $identityId;
        $this->name = $name;
        $this->email = $email;
        $this->institution = $institution;
    }

    public function jsonSerialize()
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'second_factor_id' => $this->secondFactorId,
            'status'           => (string) $this->status,
            'identity_id'      => $this->identityId,
            'name'             => $this->name,
            'email'            => $this->email,
            'institution'      => $this->institution,
        ];
    }
}
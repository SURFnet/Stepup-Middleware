<?php

/**
 * Copyright 2017 SURFnet bv
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

namespace Surfnet\Stepup\Configuration\Event;

use Broadway\Serializer\SerializableInterface;
use Surfnet\Stepup\Configuration\Value\Institution;
use Surfnet\Stepup\Configuration\Value\InstitutionConfigurationId;

class InstitutionConfigurationRemovedEvent implements SerializableInterface
{
    /**
     * @var Institution
     */
    public $institution;

    /**
     * @var InstitutionConfigurationId
     */
    public $institutionConfigurationId;

    public function __construct(InstitutionConfigurationId $institutionConfigurationId, Institution $institution)
    {
        $this->institutionConfigurationId = $institutionConfigurationId;
        $this->institution                = $institution;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(
            new InstitutionConfigurationId($data['institution_configuration_id']),
            new Institution($data['institution'])
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'institution_configuration_id' => $this->institutionConfigurationId->getInstitutionConfigurationId(),
            'institution'                  => $this->institution->getInstitution()
        ];
    }
}

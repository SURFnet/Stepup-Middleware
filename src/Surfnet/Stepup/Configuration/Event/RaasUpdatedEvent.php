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

namespace Surfnet\Stepup\Configuration\Event;

class RaasUpdatedEvent extends ConfigurationEvent
{
    /**
     * @var array
     */
    public $raas;

    /**
     * @param string $configurationId
     * @param array  $raas
     */
    public function __construct($configurationId, array $raas)
    {
        parent::__construct($configurationId);

        $this->raas = $raas;
    }

    public static function deserialize(array $data)
    {
        return new self($data['id'], $data['raas']);
    }

    public function serialize()
    {
        return ['id' => $this->id, 'raas' => $this->raas];
    }
}

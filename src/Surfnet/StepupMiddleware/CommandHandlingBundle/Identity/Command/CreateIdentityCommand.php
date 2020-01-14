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

namespace Surfnet\StepupMiddleware\CommandHandlingBundle\Identity\Command;

use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\AbstractCommand;
use Surfnet\StepupMiddleware\CommandHandlingBundle\Command\SelfServiceExecutable;
use Symfony\Component\Validator\Constraints as Assert;

class CreateIdentityCommand extends AbstractCommand implements SelfServiceExecutable
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $nameId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $institution;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $commonName;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string
     */
    public $preferredLocale;


    /**
     * @return string
     */
    public function getIdentityId()
    {
        return $this->id;
    }
}

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

namespace Surfnet\Stepup\Identity\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Surfnet\Stepup\Identity\AggregateRoot\Identity;
use Surfnet\Stepup\Identity\Command\CreateIdentityCommand;
use Surfnet\Stepup\Identity\EventSourcing\IdentityRepository;
use Surfnet\Stepup\Identity\Value\IdentityId;

class IdentityCommandHandler extends CommandHandler
{
    /**
     * @var IdentityRepository
     */
    private $repository;

    /**
     * @param IdentityRepository $repository
     */
    public function __construct(IdentityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleCreateIdentityCommand(CreateIdentityCommand $command)
    {
        $identity = Identity::create(new IdentityId($command->id));

        $this->repository->add($identity);
    }
}

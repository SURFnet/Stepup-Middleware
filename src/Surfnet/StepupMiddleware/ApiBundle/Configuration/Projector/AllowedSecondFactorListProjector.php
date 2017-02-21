<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\StepupMiddleware\ApiBundle\Configuration\Projector;

use Broadway\ReadModel\Projector;
use Surfnet\Stepup\Configuration\Event\AllowedSecondFactorListUpdatedEvent;
use Surfnet\StepupMiddleware\ApiBundle\Configuration\Entity\AllowedSecondFactor;
use Surfnet\StepupMiddleware\ApiBundle\Configuration\Repository\AllowedSecondFactorRepository;

final class AllowedSecondFactorListProjector extends Projector
{
    /**
     * @var AllowedSecondFactorRepository
     */
    private $allowedSecondFactorRepository;

    public function __construct(AllowedSecondFactorRepository $allowedSecondFactorRepository)
    {
        $this->allowedSecondFactorRepository = $allowedSecondFactorRepository;
    }

    /**
     * @param AllowedSecondFactorListUpdatedEvent $event
     */
    public function applyAllowedSecondFactorListUpdatedEvent(AllowedSecondFactorListUpdatedEvent $event)
    {
        if ($event->allowedSecondFactorList->isBlank()) {
            $this->allowedSecondFactorRepository->clearAllowedSecondFactorListFor($event->institution);
        }

        foreach ($event->allowedSecondFactorList as $secondFactor) {
            $allowedSecondFactor = AllowedSecondFactor::createFrom($event->institution, $secondFactor);
            $this->allowedSecondFactorRepository->save($allowedSecondFactor);
        }
    }
}

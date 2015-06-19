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

namespace Surfnet\StepupMiddleware\ApiBundle\Identity\Service;

use Surfnet\Stepup\Identity\Value\NameId;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\Sraa;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Repository\SraaRepository;

class SraaService
{
    /**
     * @var \Surfnet\StepupMiddleware\ApiBundle\Identity\Repository\SraaRepository
     */
    private $sraaRepository;

    public function __construct(SraaRepository $sraaRepository)
    {
        $this->sraaRepository = $sraaRepository;
    }

    /**
     * @param NameId $nameId
     * @return Sraa|null
     */
    public function findByNameId(NameId $nameId)
    {
        return $this->sraaRepository->findByNameId($nameId);
    }

    /**
     * @param NameId $nameId
     * @return bool
     */
    public function contains(NameId $nameId)
    {
        return $this->sraaRepository->contains($nameId);
    }
}

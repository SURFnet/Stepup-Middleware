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

namespace Surfnet\StepupMiddleware\ApiBundle\Identity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Command\SearchVettedSecondFactorCommand;
use Surfnet\StepupMiddleware\ApiBundle\Identity\Entity\VettedSecondFactor;

class VettedSecondFactorRepository extends EntityRepository
{
    /**
     * @param string $id
     * @return VettedSecondFactor|null
     */
    public function find($id)
    {
        /** @var VettedSecondFactor|null $secondFactor */
        $secondFactor = parent::find($id);

        return $secondFactor;
    }

    /**
     * @param SearchVettedSecondFactorCommand $command
     * @return Query
     */
    public function createSearchQuery(SearchVettedSecondFactorCommand $command)
    {
        $queryBuilder = $this->createQueryBuilder('sf');

        if ($command->identityId) {
            $queryBuilder
                ->andWhere('sf.identity = :identityId')
                ->setParameter('identityId', (string) $command->identityId);
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @param VettedSecondFactor $secondFactor
     */
    public function save(VettedSecondFactor $secondFactor)
    {
        $this->getEntityManager()->persist($secondFactor);
        $this->getEntityManager()->flush();
    }

    public function remove(VettedSecondFactor $secondFactor)
    {
        $this->getEntityManager()->remove($secondFactor);
        $this->getEntityManager()->flush();
    }
}

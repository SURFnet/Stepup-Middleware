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

namespace Surfnet\StepupMiddleware\ApiBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Surfnet\Stepup\Identity\Value\DocumentNumber;

/**
 * Custom Type for the Surfnet\Stepup\Identity\Value\DocumentNumber Object
 */
class DocumentNumberType extends Type
{
    const NAME = 'stepup_document_number';

    /**
     * @param array            $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return null|string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return null;
        }

        if (!$value instanceof DocumentNumber) {
            throw new ConversionException(
                sprintf(
                    "Encountered illegal document number of type %s '%s', expected a DocumentNumber instance",
                    is_object($value) ? get_class($value) : gettype($value),
                    is_scalar($value) ? (string) $value : ''
                )
            );
        }

        return $value->getDocumentNumber();
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     * @return null|DocumentNumber
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return null;
        }

        return new DocumentNumber($value);
    }

    public function getName()
    {
        return self::NAME;
    }
}

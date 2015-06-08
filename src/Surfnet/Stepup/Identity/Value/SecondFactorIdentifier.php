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

namespace Surfnet\Stepup\Identity\Value;

interface SecondFactorIdentifier
{
    /**
     * @return static
     */
    public static function unknown();

    /**
     * Return a string representation of the value of this value object.
     *
     * @return string
     */
    public function getValue();

    /**
     * @param SecondFactorIdentifier $other
     * @return bool
     */
    public function equals(SecondFactorIdentifier $other);

    /**
     * @return string
     */
    public function __toString();
}
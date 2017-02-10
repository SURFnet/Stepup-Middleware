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

namespace Surfnet\Stepup\Tests\Configuration\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Stepup\Configuration\Value\AllowedSecondFactorList;
use Surfnet\StepupBundle\Value\SecondFactorType;

class AllowedSecondFactorListTest extends TestCase
{
    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_deduplicates_its_second_factors_upon_creation()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('sms'),
        ]);

        $this->assertCount(
            1,
            iterator_to_array($allowedSecondFactorList),
            'The AllowedSecondFactorList should have deduplicated its second factors, but it did not'
        );
    }

    /**
     * @test
     * @group domain
     *
     * @dataProvider availableSecondFactorTypeProvider
     * @param $availableSecondFactorType
     */
    public function a_blank_allowed_second_factor_list_allows_all_second_factors($availableSecondFactorType)
    {
        $allowedSecondFactorList = AllowedSecondFactorList::blank();

        $isSecondFactorAllowed = $allowedSecondFactorList->allows($availableSecondFactorType);

        $this->assertTrue($isSecondFactorAllowed);
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_contains_a_given_second_factor()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([new SecondFactorType('sms')]);
        $allowedSecondFactor     = new SecondFactorType('sms');

        $containsSecondFactor = $allowedSecondFactorList->contains($allowedSecondFactor);

        $this->assertTrue(
            $containsSecondFactor,
            'An allowed second factor list should contain a listed second factor but it does not'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_does_not_contain_a_given_second_factor()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([new SecondFactorType('sms')]);
        $allowedSecondFactor     = new SecondFactorType('yubikey');

        $containsSecondFactor = $allowedSecondFactorList->contains($allowedSecondFactor);

        $this->assertFalse(
            $containsSecondFactor,
            'An allowed second factor list should not contain a listed second factor but it does'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_contains_the_given_second_factors()
    {
        $secondFactorTypes = [
            new SecondFactorType('sms'),
            new SecondFactorType('yubikey'),
        ];

        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes($secondFactorTypes);

        foreach ($allowedSecondFactorList as $index => $actualSecondFactorType) {
            $this->assertTrue($secondFactorTypes[$index]->equals($actualSecondFactorType));
        }
    }

    /**
     * @test
     * @group domain
     */
    public function a_second_factor_on_the_allowed_second_factor_list_is_allowed()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([new SecondFactorType('sms')]);
        $allowedSecondFactor     = new SecondFactorType('sms');

        $isSecondFactorAllowed = $allowedSecondFactorList->allows($allowedSecondFactor);

        $this->assertTrue(
            $isSecondFactorAllowed,
            'An allowed second factor list should allow a listed second factor but it does not'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function a_second_factor_not_on_the_allowed_second_factor_list_is_not_allowed()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([new SecondFactorType('sms')]);
        $disallowedSecondFactor  = new SecondFactorType('yubikey');

        $isSecondFactorAllowed = $allowedSecondFactorList->allows($disallowedSecondFactor);

        $this->assertFalse(
            $isSecondFactorAllowed,
            'An allowed second factor list should not allow an unlisted second factor but it does not'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_equals_an_allowed_second_factor_list_with_the_same_values()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('yubikey'),
        ]);
        $sameAllowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('yubikey'),
        ]);

        $isSameAllowedSecondFactorList = $allowedSecondFactorList->equals($sameAllowedSecondFactorList);

        $this->assertTrue(
            $isSameAllowedSecondFactorList,
            'An allowed second factor list should equal a second factor list with the same values but it does not'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_does_not_equal_an_allowed_second_factor_list_with_different_values()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('yubikey'),
        ]);
        $differentAllowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('u2f'),
        ]);

        $isSameAllowedSecondFactorList = $allowedSecondFactorList->equals($differentAllowedSecondFactorList);

        $this->assertFalse(
            $isSameAllowedSecondFactorList,
            'An allowed second factor list should not equal a second factor list with different values but it does'
        );
    }

    /**
     * @test
     * @group domain
     */
    public function an_allowed_second_factor_list_does_not_equal_an_allowed_second_factor_list_with_a_different_amount_of_values()
    {
        $allowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('yubikey'),
            new SecondFactorType('u2f'),
        ]);
        $differentAllowedSecondFactorList = AllowedSecondFactorList::ofTypes([
            new SecondFactorType('sms'),
            new SecondFactorType('u2f'),
        ]);

        $isSameAllowedSecondFactorList = $allowedSecondFactorList->equals($differentAllowedSecondFactorList);

        $this->assertFalse(
            $isSameAllowedSecondFactorList,
            'An allowed second factor list should not equal a second factor list with a different amount of values but it does'
        );
    }

    public function availableSecondFactorTypeProvider()
    {
        $secondFactorTypes = array_map(function ($availableSecondFactorType) {
            return [new SecondFactorType($availableSecondFactorType)];
        }, SecondFactorType::getAvailableSecondFactorTypes());

        return array_combine(SecondFactorType::getAvailableSecondFactorTypes(), $secondFactorTypes);
    }
}

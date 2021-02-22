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

namespace Surfnet\StepupMiddleware\ApiBundle\Tests\Doctrine\Type;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase as UnitTest;
use Surfnet\Stepup\Identity\Value\Location;
use Surfnet\StepupMiddleware\ApiBundle\Doctrine\Type\LocationType;

class LocationTypeTest extends UnitTest
{
    /**
     * @var \Doctrine\DBAL\Platforms\MySqlPlatform
     */
    private $platform;

    /**
     * Register the type, since we're forced to use the factory method.
     */
    public static function setUpBeforeClass(): void
    {
        Type::addType(
            LocationType::NAME,
            'Surfnet\StepupMiddleware\ApiBundle\Doctrine\Type\LocationType'
        );
    }

    public function setUp(): void
    {
        $this->platform = new MySqlPlatform();
    }

    /**
     * @test
     * @group doctrine
     */
    public function a_null_value_remains_null_in_to_sql_conversion()
    {
        $location = Type::getType(LocationType::NAME);

        $value = $location->convertToDatabaseValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_the_correct_format()
    {
        $location = Type::getType(LocationType::NAME);

        $expected = 'Somewhere behind you';
        $input    = new Location($expected);
        $output   = $location->convertToDatabaseValue($input, $this->platform);

        $this->assertTrue(is_string($output));
        $this->assertEquals($expected, $output);
    }

    /**
     * @test
     * @group doctrine
     */
    public function a_null_value_remains_null_when_converting_from_db_to_php_value()
    {
        $location = Type::getType(LocationType::NAME);

        $value = $location->convertToPHPValue(null, $this->platform);

        $this->assertNull($value);
    }

    /**
     * @test
     * @group doctrine
     */
    public function a_non_null_value_is_converted_to_a_location_value_object()
    {
        $location = Type::getType(LocationType::NAME);

        $input = 'Call me Maybe';

        $output = $location->convertToPHPValue($input, $this->platform);

        $this->assertInstanceOf('Surfnet\Stepup\Identity\Value\Location', $output);
        $this->assertEquals(new Location($input), $output);
    }

    /**
     * @test
     * @group doctrine
     */
    public function an_invalid_database_value_causes_an_exception_upon_conversion()
    {
        $this->expectException(\Doctrine\DBAL\Types\ConversionException::class);

        $location = Type::getType(LocationType::NAME);

        $location->convertToPHPValue(false, $this->platform);
    }
}

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

namespace Surfnet\StepupMiddleware\ApiBundle\Tests\Request;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\StepupMiddleware\ApiBundle\Request\CommandParamConverter;

class CommandParamConverterTest extends TestCase
{
    /**
     * @test
     * @group api-bundle
     * @dataProvider invalidCommandJsonStructures
     * @param string $commandJson
     */
    public function it_validates_the_command_structure($commandJson)
    {
        $this->expectException(\Surfnet\StepupMiddleware\ApiBundle\Exception\BadCommandRequestException::class);

        $request = m::mock('Symfony\Component\HttpFoundation\Request')
            ->shouldReceive('getContent')->with()->andReturn($commandJson)
            ->getMock();
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $converter = new CommandParamConverter();
        $converter->apply($request, $configuration);
    }

    /**
     * @test
     * @group api-bundle
     * @dataProvider convertibleCommandNames
     * @param string $expectedCommandClass
     * @param string $commandName
     */
    public function it_can_convert_command_name_notation($expectedCommandClass, $commandName)
    {
        $command = ['command' => ['name' => $commandName, 'uuid' => 'abcdef', 'payload' => new \stdClass]];

        $request = m::mock('Symfony\Component\HttpFoundation\Request')
            ->shouldReceive('getContent')->with()->andReturn(json_encode($command))
            ->getMock();
        $request->attributes = m::mock()
            ->shouldReceive('set')->with('command', m::type($expectedCommandClass))
            ->getMock();
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $converter = new CommandParamConverter();
        $converter->apply($request, $configuration);

        $this->assertInstanceOf(CommandParamConverter::class, $converter);
    }

    /**
     * @test
     * @group api-bundle
     */
    public function it_sets_uuid()
    {
        $command = ['command' => ['name' => 'Root:FooBar', 'uuid' => 'abcdef', 'payload' => new \stdClass]];

        $request = m::mock('Symfony\Component\HttpFoundation\Request')
            ->shouldReceive('getContent')->with()->andReturn(json_encode($command))
            ->getMock();
        $request->attributes = m::mock()
            ->shouldReceive('set')->with('command', self::spy($spiedCommand))
            ->getMock();
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $converter = new CommandParamConverter();
        $converter->apply($request, $configuration);

        $this->assertEquals('abcdef', $spiedCommand->UUID, 'UUID mismatch');
    }

    /**
     * @test
     * @group api-bundle
     */
    public function it_sets_payload()
    {
        $command = ['command' => ['name' => 'Root:FooBar', 'uuid' => 'abcdef', 'payload' => ['snake_case' => true]]];

        $request = m::mock('Symfony\Component\HttpFoundation\Request')
            ->shouldReceive('getContent')->with()->andReturn(json_encode($command))
            ->getMock();
        $request->attributes = m::mock()
            ->shouldReceive('set')->with('command', self::spy($spiedCommand))
            ->getMock();
        $configuration = m::mock('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter');

        $converter = new CommandParamConverter();
        $converter->apply($request, $configuration);

        $spiedPayload = (array) $spiedCommand;
        unset($spiedPayload['UUID']);
        $this->assertSame(['snakeCase' => true], $spiedPayload, 'Payload mismatch');
    }

    public function invalidCommandJsonStructures()
    {
        return array_map(
            function ($command) {
                return [json_encode($command)];
            },
            [
                'Body may not be null' => null,
                'Body may not be integer' => 1,
                'Body may not be float' => 1.1,
                'Body may not be array' => [],
                'Object must contain command property' => new \stdClass,
                'Command may not be null' => ['command' => null],
                'Command may not be integer' => ['command' => 1],
                'Command may not be float' => ['command' => 1.1],
                'Command may not be array' => ['command' => []],
                'Command must contain name' => ['command' => ['uuid' => 'foo', 'payload' => 'bar']],
                'Command must contain uuid' => ['command' => ['name' => 'quux', 'payload' => 'wibble']],
                'Command must contain payload' => ['command' => ['name' => 'wobble', 'uuid' => 'wubble']],
                'Command payload may not be null' => ['command' => ['payload' => null]],
                'Command payload may not be integer' => ['command' => ['payload' => 1]],
                'Command payload may not be float' => ['command' => ['payload' => 1.1]],
                'Command payload may not be array' => ['command' => ['payload' => []]],
            ]
        );
    }

    public function convertibleCommandNames()
    {
        return [
            'It can convert simple command notation with a namespace' => [
                'Surfnet\StepupMiddleware\CommandHandlingBundle\Root\Command\FooBarCommand', 'Root:FooBar',
            ],
            'It can convert simple command notation with a namespace with trailing backslash' => [
                'Surfnet\StepupMiddleware\CommandHandlingBundle\Root\Command\FooBarCommand', 'Root:FooBar',
            ],
            'It can convert namespaced command notation with a namespace' => [
                'Surfnet\StepupMiddleware\CommandHandlingBundle\Root\Command\Ns\QuuxCommand', 'Root:Ns.Quux',
            ],
        ];
    }

    /**
     * @param mixed &$spy
     * @return \Mockery\Matcher\MatcherAbstract
     */
    private static function spy(&$spy)
    {
        return m::on(
            function ($value) use (&$spy) {
                $spy = $value;

                return true;
            }
        );
    }
}

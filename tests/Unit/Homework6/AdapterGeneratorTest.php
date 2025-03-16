<?php

namespace Tests\Unit\Homework6;

use App\Homework2\Vector;
use App\Homework3\ICommand;
use App\Homework5\InitScopeCommand;
use App\Homework5\IoC;
use App\Homework6\AdapterGenerator;
use App\Homework6\CamelCaseMethodParser;
use App\Homework6\DependencyNameComposer;
use App\Homework6\IMovable;
use App\Homework6\IMovableExt;
use App\Homework6\UObject;
use Codeception\Test\Unit;
use InvalidArgumentException;
use Tests\Support\UnitTester;

class AdapterGeneratorTest extends Unit
{
    protected UnitTester $tester;
    protected AdapterGenerator $sut;

    public function testBaseMovableInterfaceAdapter(): void
    {
        $objMock = $this->makeEmpty(UObject::class);

        $getPositionVector = new Vector(1, 2);
        $getVelocityVector = new Vector(3, 4);
        $objMock->expects($this->exactly(2))
            ->method('getProperty')
            ->willReturnMap([
                ['Position', $getPositionVector],
                ['Velocity', $getVelocityVector],
            ]);
        IoC::resolve('IoC.Register', 'IMovable.Position.Get', static function (UObject $obj) {
            return $obj->getProperty('Position');
        })->execute();
        IoC::resolve('IoC.Register', 'IMovable.Velocity.Get', static function (UObject $obj) {
            return $obj->getProperty('Velocity');
        })->execute();

        $setPositionVector = new Vector(5, 6);
        $objMock->expects($this->once())
            ->method('setProperty')
            ->with('Position', $setPositionVector);
        IoC::resolve(
            'IoC.Register',
            'IMovable.Position.Set',
            static function (UObject $obj, Vector $position): ICommand {
                return new class($obj, $position) implements ICommand {
                    private UObject $obj;
                    private Vector $position;

                    public function __construct(UObject $obj, Vector $position)
                    {
                        $this->obj = $obj;
                        $this->position = $position;
                    }

                    public function execute(): void
                    {
                        $this->obj->setProperty('Position', $this->position);
                    }
                };
            }
        )->execute();

        /** @var IMovable $adapter */
        $adapter = IoC::resolve('Adapter', IMovable::class, $objMock);

        $this->tester->assertSame($getPositionVector, $adapter->getPosition());
        $this->tester->assertSame($getVelocityVector, $adapter->getVelocity());
        $adapter->setPosition($setPositionVector);
    }

    public function testExtendedMovableInterfaceAdapter(): void
    {
        $objMock = $this->makeEmpty(UObject::class);

        $objMock->expects($this->once())
            ->method('getProperty')
            ->with('Finish')
            ->willReturn(456);
        IoC::resolve('IoC.Register', 'IMovableExt.Finish', static function (UObject $obj) {
            return $obj->getProperty('Finish');
        })->execute();

        $objMock->expects($this->once())
            ->method('setProperty')
            ->with('Normalize', '123');
        IoC::resolve(
            'IoC.Register',
            'IMovableExt.Normalize',
            static function (UObject $obj, string $number): ICommand {
                return new class($obj, $number) implements ICommand {
                    private UObject $obj;
                    private string $number;

                    public function __construct(UObject $obj, string $number)
                    {
                        $this->obj = $obj;
                        $this->number = $number;
                    }

                    public function execute(): void
                    {
                        $this->obj->setProperty('Normalize', $this->number);
                    }
                };
            }
        )->execute();

        /** @var IMovableExt $adapter */
        $adapter = IoC::resolve('Adapter', IMovableExt::class, $objMock);

        $this->tester->assertSame(456, $adapter->finish());
        $adapter->normalize();
    }

    public function testInterfaceDoesNotExist(): void
    {
        $objMock = $this->makeEmpty(UObject::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Interface UnknownInterface does not exist.');

        IoC::resolve('Adapter', 'UnknownInterface', $objMock);
    }

    protected function _before(): void
    {
        (new InitScopeCommand())->execute();

        $methodParser = new CamelCaseMethodParser();
        $depComposer = new DependencyNameComposer();
        $this->sut = new AdapterGenerator($methodParser, $depComposer);

        IoC::resolve('IoC.Register', 'Adapter', function (string $interfaceName, UObject $object) {
            return $this->sut->generateAdapter($interfaceName, $object);
        })->execute();
    }
}

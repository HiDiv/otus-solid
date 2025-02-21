<?php

namespace Tests\Unit\Homework4;

use App\Homework4\CheckFuelCommand;
use App\Homework4\CommandException;
use App\Homework4\IFuelObject;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class CheckFuelCommandTest extends Unit
{
    protected UnitTester $tester;
    protected $fuelMock;
    protected CheckFuelCommand $sut;

    public function testSuccessExecute(): void
    {
        $this->fuelMock->expects($this->once())->method('getFuelVolume')->willReturn(100);
        $this->fuelMock->expects($this->once())->method('getOneTimeVolume')->willReturn(20);
        $this->fuelMock->expects($this->never())->method('setFuelVolume');

        $this->sut->execute();
    }

    public function testFailExecute(): void
    {
        $this->expectException(CommandException::class);

        $this->fuelMock->expects($this->once())->method('getFuelVolume')->willReturn(10);
        $this->fuelMock->expects($this->once())->method('getOneTimeVolume')->willReturn(30);
        $this->fuelMock->expects($this->never())->method('setFuelVolume');

        $this->sut->execute();
    }

    protected function _before(): void
    {
        $this->fuelMock = $this->makeEmpty(IFuelObject::class);
        $this->sut = new CheckFuelCommand($this->fuelMock);
    }
}

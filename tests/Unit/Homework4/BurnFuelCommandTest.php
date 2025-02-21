<?php

namespace Tests\Unit\Homework4;

use App\Homework4\BurnFuelCommand;
use App\Homework4\IFuelObject;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class BurnFuelCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testExecute(): void
    {
        $fuelMock = $this->makeEmpty(IFuelObject::class);
        $fuelMock->expects($this->once())->method('getFuelVolume')->willReturn(100);
        $fuelMock->expects($this->once())->method('getOneTimeVolume')->willReturn(20);
        $fuelMock->expects($this->once())->method('setFuelVolume')->with(80);

        $sut = new BurnFuelCommand($fuelMock);

        $sut->execute();
    }
}

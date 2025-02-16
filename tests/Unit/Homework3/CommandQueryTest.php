<?php

namespace Tests\Unit\Homework3;

use App\Homework3\CommandQuery;
use App\Homework3\ICommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class CommandQueryTest extends Unit
{
    protected UnitTester $tester;
    protected CommandQuery $sut;

    public function testEmptyQuery(): void
    {
        $result = $this->sut->take();

        $this->tester->assertNull($result);
    }

    public function testPush(): void
    {
        $command1 = $this->makeEmpty(ICommand::class);
        $command2 = $this->makeEmpty(ICommand::class);

        $this->sut->push($command1);
        $this->sut->push($command2);

        $this->tester->assertEquals($command1, $this->sut->take());
        $this->tester->assertEquals($command2, $this->sut->take());
        $this->tester->assertNull($this->sut->take());
    }

    protected function _before(): void
    {
        $this->sut = new CommandQuery();
    }
}

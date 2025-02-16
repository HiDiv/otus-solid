<?php

namespace Tests\Unit\Homework3;

use App\Homework3\CommandPushToQuery;
use App\Homework3\CommandQuery;
use App\Homework3\ICommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class CommandPushToQueryTest extends Unit
{
    protected UnitTester $tester;

    public function testExecute(): void
    {
        $command = $this->makeEmpty(ICommand::class);

        $query = $this->makeEmpty(CommandQuery::class);
        $query->expects($this->once())->method('push')->with($command);

        $sut = new CommandPushToQuery($command, $query);
        $sut->execute();
    }
}

<?php

namespace Tests\Unit\Homework3;

use App\Homework3\ILog;
use App\Homework3\WriteLogCommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class WriteLogCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testWriteLogCommand(): void
    {
        $message = 'Test message';

        $log = $this->makeEmpty(ILog::class);
        $log->expects($this->once())->method('log')->with($message);

        $sut = new WriteLogCommand($log, $message);
        $sut->execute();
    }
}

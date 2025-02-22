<?php

namespace Tests\Unit\Homework3;

use App\Homework3\CommandException;
use App\Homework3\CommandFirstRepeat;
use App\Homework3\CommandPushToQuery;
use App\Homework3\CommandQuery;
use App\Homework3\CommandRepeater;
use App\Homework3\ExceptionHandler;
use App\Homework3\ICommand;
use App\Homework3\ILog;
use App\Homework3\QueueHandler;
use App\Homework3\WriteLogCommand;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;
use Throwable;

class QueueHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected CommandQuery $queue;
    protected ExceptionHandler $exceptionHandler;
    protected QueueHandler $sut;

    public function testPushWriteLogCommandToQuery(): void
    {
        $executionOrder = [];

        $exceptMessage = 'test exception';
        $expectException = new Exception($exceptMessage);

        $exceptionCommand = $this->makeEmpty(ICommand::class);
        $exceptionCommand->expects($this->once())->method('execute')->willThrowException($expectException);
        $this->queue->push($exceptionCommand);

        $log = $this->makeEmpty(ILog::class);
        $log->expects($this->once())->method('log')->with($exceptMessage);

        $this->exceptionHandler->register(
            get_class($exceptionCommand),
            get_class($expectException),
            function (ICommand $command, Throwable $exception) use ($log, &$executionOrder): ICommand {
                $executionOrder[] = 'PushToQueryWriteLogCommand';
                $command = new WriteLogCommand($log, $exception->getMessage());
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $this->sut->handle();

        $this->tester->assertEquals(['PushToQueryWriteLogCommand'], $executionOrder);
    }

    public function testThrowFirstTimeRepeatThrowAgainWriteToLog(): void
    {
        $executionOrder = [];

        $exceptMsg = 'test exception';
        $exception = new Exception($exceptMsg);
        $commandException = new CommandException($exception);
        $this->queue->push($commandException);

        $this->exceptionHandler->register(
            CommandException::class,
            get_class($exception),
            function (ICommand $command, Throwable $exception) use (&$executionOrder) {
                $executionOrder[] = 'PushToQueryCommandRepeater';
                $command = new CommandRepeater($command);
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $log = $this->makeEmpty(ILog::class);
        $log->expects($this->once())->method('log')->with($exceptMsg);

        $this->exceptionHandler->register(
            CommandRepeater::class,
            get_class($exception),
            function (ICommand $command, Throwable $exception) use ($log, &$executionOrder) {
                $executionOrder[] = 'PushToQueryWriteLog';
                $command = new WriteLogCommand($log, $exception->getMessage());
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $this->sut->handle();

        $this->tester->assertEquals(['PushToQueryCommandRepeater', 'PushToQueryWriteLog'], $executionOrder);
    }

    public function testThrowRepeatTwiceThrowAgainWriteToLog(): void
    {
        $executionOrder = [];

        $exceptMsg = 'test exception';
        $exception = new Exception($exceptMsg);
        $commandException = new CommandException($exception);
        $this->queue->push($commandException);

        $this->exceptionHandler->register(
            CommandException::class,
            get_class($exception),
            function (ICommand $command, Throwable $exception) use (&$executionOrder) {
                $executionOrder[] = 'PushToQueryFirstCommandRepeat';
                $command = new CommandFirstRepeat($command);
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $this->exceptionHandler->register(
            CommandFirstRepeat::class,
            get_class($exception),
            function (ICommand $command, Throwable $exception) use (&$executionOrder) {
                $executionOrder[] = 'PushToQuerySecondCommandRepeat';
                $command = new CommandRepeater($command);
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $log = $this->makeEmpty(ILog::class);
        $log->expects($this->once())->method('log')->with($exceptMsg);

        $this->exceptionHandler->register(
            CommandRepeater::class,
            get_class($exception),
            function (ICommand $command, Throwable $exception) use ($log, &$executionOrder) {
                $executionOrder[] = 'PushToQueryWriteLog';
                $command = new WriteLogCommand($log, $exception->getMessage());
                return new CommandPushToQuery($command, $this->queue);
            }
        );

        $this->sut->handle();

        $this->tester->assertEquals(
            ['PushToQueryFirstCommandRepeat', 'PushToQuerySecondCommandRepeat', 'PushToQueryWriteLog'],
            $executionOrder
        );
    }

    protected function _before(): void
    {
        $this->queue = new CommandQuery();
        $this->exceptionHandler = new ExceptionHandler();
        $this->sut = new QueueHandler($this->queue, $this->exceptionHandler);
    }
}

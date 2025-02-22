<?php

namespace Tests\Unit\Homework3;

use App\Homework3\ExceptionHandler;
use App\Homework3\ICommand;
use Codeception\Test\Unit;
use Exception;
use RuntimeException;
use Tests\Support\UnitTester;
use Throwable;

class ExceptionHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected ExceptionHandler $sut;

    public static function handleDataProvider(): array
    {
        $sut = new ExceptionHandler();
        $command = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $exception = new Exception('test');
        $commandHandler = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $sut->register(get_class($command), get_class($exception), function () use ($commandHandler) {
            return $commandHandler;
        });

        $otherCommand = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $defaultCommandHandler = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $sut->register(get_class($command), '', function () use ($defaultCommandHandler) {
            return $defaultCommandHandler;
        });

        $otherException = new RuntimeException('test');
        $defaultExceptionHandler = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $sut->register('', get_class($exception), function () use ($defaultExceptionHandler) {
            return $defaultExceptionHandler;
        });

        $defaultHandler = new class implements ICommand {
            public function execute(): void
            {
            }
        };
        $sut->register('', '', function () use ($defaultHandler) {
            return $defaultHandler;
        });

        return [
            'Есть обработчик для команды и исключения' => [
                'sut' => $sut,
                'command' => $command,
                'exception' => $exception,
                'expect' => $commandHandler,
            ],
            'Есть обработчик по умолчанию для команды' => [
                'sut' => $sut,
                'command' => $command,
                'exception' => $otherException,
                'expect' => $defaultCommandHandler,
            ],
            'Есть обработчик по умолчанию для исключения' => [
                'sut' => $sut,
                'command' => $otherCommand,
                'exception' => $exception,
                'expect' => $defaultExceptionHandler,
            ],
            'Есть общий обработчик по умолчанию' => [
                'sut' => $sut,
                'command' => $otherCommand,
                'exception' => $otherException,
                'expect' => $defaultHandler,
            ],
        ];
    }

    public function testRegister(): void
    {
        $commandHandler1 = $this->makeEmpty(ICommand::class);
        $commandHandler2 = $this->makeEmpty(ICommand::class);

        $command = $this->makeEmpty(ICommand::class);
        $exception = $this->makeEmpty(Exception::class);

        $this->sut->register(get_class($command), get_class($exception), function () use ($commandHandler1) {
            return $commandHandler1;
        });
        $this->sut->register(get_class($command), get_class($exception), function () use ($commandHandler2) {
            return $commandHandler2;
        });
        $result = $this->sut->handle($command, $exception);

        $this->tester->assertNotSame($commandHandler1, $result);
        $this->tester->assertSame($commandHandler2, $result);
    }

    public function testHandleNotFound(): void
    {
        $command = $this->makeEmpty(ICommand::class);
        $exception = $this->makeEmpty(Exception::class);
        $this->expectExceptionObject($exception);

        $this->sut->handle($command, $exception);
    }

    /**
     * @param ExceptionHandler $sut
     * @param ICommand $command
     * @param Throwable $exception
     * @param ICommand $expect
     * @dataProvider handleDataProvider
     * @throws Throwable
     */
    public function testHandle(ExceptionHandler $sut, ICommand $command, Throwable $exception, ICommand $expect): void
    {
        $this->tester->assertSame($expect, $sut->handle($command, $exception));
    }

    protected function _before(): void
    {
        $this->sut = new ExceptionHandler();
    }
}

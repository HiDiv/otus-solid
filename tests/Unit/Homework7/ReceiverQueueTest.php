<?php

namespace Tests\Unit\Homework7;

use App\Homework3\ICommand;
use App\Homework7\EmptyCommand;
use App\Homework7\ReceiverQueue;
use Codeception\Test\Unit;
use parallel\Channel;
use parallel\Runtime;
use Tests\Support\UnitTester;

class ReceiverQueueTest extends Unit
{
    protected UnitTester $tester;

    private string $channelName = 'test_queue';

    public function testReceiveBlocksUntilMessage(): void
    {
        $queue = new ReceiverQueue($this->channelName);

        $runtime = new Runtime();

        // Объект-команда для передачи
        $command = new class implements ICommand {
            public function execute(): void
            {
            }
        };

        // В отдельном потоке положим команду через небольшую задержку
        $runtime->run(function (string $chanName) {
            require_once __DIR__ . '/../../../vendor/autoload.php';

            // Объект-команда для передачи
            $command = new EmptyCommand();

            usleep(500000); // 500 мс, чтобы main поток точно начал ждать
            $chan = Channel::open($chanName);
            $chan->send($command);
        }, [$this->channelName]);

        // Теперь main-поток будет блокироваться, пока команда не появится
        $receivedCommand = $queue->receive();

        $this->tester->assertInstanceOf(EmptyCommand::class, $receivedCommand);
    }

    public function testSequentialReceiveOrder(): void
    {
        $sut = new ReceiverQueue($this->channelName);

        $a = new class implements ICommand {
            private string $mark = 'first';

            public function execute(): void
            {
            }
        };
        $b = new class implements ICommand {
            private string $mark = 'second';

            public function execute(): void
            {
            }
        };

        // отправляем два объекта в очередь
        Channel::open($this->channelName)->send($a);
        Channel::open($this->channelName)->send($b);

        // извлекаем по очереди
        $first = $sut->receive();
        $second = $sut->receive();

        $this->tester->assertEquals($a, $first, 'Первым должен вернуться первый отправленный объект');
        $this->tester->assertNotSame($a, $first, 'Объекты должны быть одного вида, но не одинаковые из-за serialize и unserialize');
        $this->tester->assertEquals($b, $second, 'Вторым — второй объект');
        $this->tester->assertNotSame($b, $second, 'Объекты должны быть одного вида, но не одинаковые из-за serialize и unserialize');
    }

    public function testEmptyDoesNotBlockAndDoesNotConsume(): void
    {
        $queue = new ReceiverQueue($this->channelName);
        // очередь сейчас пуста
        $this->assertTrue($queue->empty(), 'empty() должен вернуть true на пустой очереди');

        // пустая empty() не должна съесть элемент, проверим:
        // положим команду
        $cmd = new EmptyCommand();
        Channel::open($this->channelName)->send($cmd);

        // сразу проверим empty() — очередь не пуста
        $this->assertFalse($queue->empty(), 'empty() должен вернуть false, когда объект есть');

        // и при этом receive() должен вернуть тот же самый объект
        $received = $queue->receive();
        $this->tester->assertEquals($cmd, $received, 'receive() должен вернуть объект, не потерянный empty()');
    }

    public function testDoubleCheck(): void
    {
        $sut = new ReceiverQueue($this->channelName);
        $cmd = new EmptyCommand();
        Channel::open($this->channelName)->send($cmd);

        $firstCheck = $sut->empty();
        $secondCheck = $sut->empty();

        $this->tester->assertFalse($firstCheck, 'очередь не пустая');
        $this->tester->assertFalse($secondCheck, 'повторный empty() должен вернуть false');
    }

    protected function _before(): void
    {
        // Перед каждым тестом (пере)создаём именованный канал
        // Если канал уже существует в ZTS-PHP, закрываем и создаём заново:
        try {
            Channel::open($this->channelName)->close();
        } catch (\Throwable $e) {
            // игнорируем, если канала не было
        }
        Channel::make($this->channelName, Channel::Infinite);
    }
}

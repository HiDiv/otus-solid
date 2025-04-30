<?php

namespace Tests\Unit\Homework7;

use App\Homework7\EmptyCommand;
use App\Homework7\HardStopExceptionCommand;
use App\Homework7\ReceiverQueue;
use App\Homework7\SoftStopExceptionCommand;
use App\Homework7\StartServerThreadCommand;
use Codeception\Test\Unit;
use parallel\Channel;
use parallel\Runtime;
use Tests\Support\UnitTester;
use Throwable;

class StartServerThreadCommandTest extends Unit
{
    protected UnitTester $tester;
    protected string $channelName = 'test_queue';
    protected Runtime $runtime;
    protected StartServerThreadCommand $sut;

    public function testHardStop(): void
    {
        $this->sut->execute();

        usleep(100000); // 100 мс, чтобы поток точно начал ждать

        $emptyCommand = new EmptyCommand();
        $hardStopCommand = new HardStopExceptionCommand();

        $chan = Channel::open($this->channelName);
        $chan->send($hardStopCommand);
        $chan->send($emptyCommand);

        usleep(400000); // 400 мс, чтобы поток точно завершился
        $receiver = new ReceiverQueue($this->channelName);

        $this->tester->assertFalse($receiver->empty(), 'Очередь не должна быть пуста');
        $this->tester->assertInstanceOf(EmptyCommand::class, $receiver->receive(), 'Следующая на обработку команда EmptyCommand');
    }

    public function testSortStop(): void
    {
        $this->sut->execute();

        $emptyCommand = new EmptyCommand();
        $softStopCommand = new SoftStopExceptionCommand();

        $chan = Channel::open($this->channelName);
        $chan->send($softStopCommand);
        $chan->send($emptyCommand);

        usleep(400000); // 400 мс, чтобы поток точно завершился
        $receiver = new ReceiverQueue($this->channelName);

        $this->tester->assertTrue($receiver->empty(), 'Очередь должна быть пуста');
    }

    public function _before(): void
    {
        // Перед каждым тестом (пере)создаём именованный канал
        // Если канал уже существует в ZTS-PHP, закрываем и создаём заново:
        try {
            Channel::open($this->channelName)->close();
        } catch (Throwable $e) {
            // игнорируем, если канала не было
        }
        Channel::make($this->channelName, Channel::Infinite);

        $this->runtime = new Runtime();

        $this->sut = new StartServerThreadCommand($this->runtime, $this->channelName);
    }
}

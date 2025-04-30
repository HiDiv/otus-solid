<?php

namespace App\Homework7;

use App\Homework3\ICommand;
use parallel\Channel;
use parallel\Events;
use parallel\Events\Event\Type;

class ReceiverQueue implements ReceiverInterface
{
    private Channel $queue;
    private Events $events;
    private ?ICommand $peeked = null;

    public function __construct(string $channelName)
    {
        $this->queue = Channel::open($channelName);

        $this->events = new Events();
        $this->events->addChannel($this->queue);
        $this->events->setBlocking(false);
    }

    public function receive(): ICommand
    {
        // если в empty() уже прочитали команду — отдадим её
        if ($this->peeked !== null) {
            $cmd = $this->peeked;
            $this->peeked = null;
            return $cmd;
        }

        return $this->queue->recv();
    }

    public function empty(): bool
    {
        if (null !== $this->peeked) {
            return false;
        }

        $event = $this->events->poll();

        // если ничего нет — очередь пуста
        if ($event === null || $event->type !== Type::Read) {
            return true;
        }

        // если есть событие «read», вытянем значение из события и запомним его
        /** @var ICommand $cmd */
        $cmd = $event->value;
        $this->peeked = $cmd;

        // не блокируем, но говорим «не пусто»
        return false;
    }
}

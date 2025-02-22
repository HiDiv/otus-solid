<?php

namespace App\Homework4;

use App\Homework2\IMovingObject;
use App\Homework3\ICommand;

class MoveCommand implements ICommand
{
    private IMovingObject $movingObject;
    public const string ZERO_VELOCITY_ERR_MSG = 'Невозможно переместить объект с нулевой скоростью.';

    public function __construct(IMovingObject $movingObject)
    {
        $this->movingObject = $movingObject;
    }

    /**
     * @throws CommandException
     */
    public function execute(): void
    {
        $velocity = $this->movingObject->getVelocity();
        if ($velocity->getDx() === 0 && $velocity->getDy() === 0) {
            throw new CommandException(self::ZERO_VELOCITY_ERR_MSG);
        }

        $this->movingObject->setLocation($this->movingObject->getLocation()->plusVector($velocity));
    }
}

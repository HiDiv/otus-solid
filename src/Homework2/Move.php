<?php

namespace App\Homework2;

class Move
{
    private IMovingObject $movingObject;

    public function __construct(IMovingObject $movingObject)
    {
        $this->movingObject = $movingObject;
    }

    public function execute(): void
    {
        $this->movingObject->setLocation(
            $this->movingObject->getLocation()->plusVector($this->movingObject->getVelocity())
        );
    }
}

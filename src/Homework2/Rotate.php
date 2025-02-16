<?php

namespace App\Homework2;

class Rotate
{
    private IRotatingObject $rotatingObject;

    public function __construct(IRotatingObject $rotatingObject)
    {
        $this->rotatingObject = $rotatingObject;
    }

    public function execute(): void
    {
        $this->rotatingObject->setAngle(
            $this->rotatingObject->getAngle()->plus($this->rotatingObject->getAngularVelocity())
        );
    }
}

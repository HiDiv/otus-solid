<?php

namespace App\Homework4;

use App\Homework2\IRotatingObject;
use App\Homework3\ICommand;
use Throwable;

class RotateCommand implements ICommand
{
    private IRotatingObject $rotatingObject;

    public function __construct(IRotatingObject $rotatingObject)
    {
        $this->rotatingObject = $rotatingObject;
    }

    /**
     * @throws CommandException
     */
    public function execute(): void
    {
        try {
            $this->rotatingObject->setAngle(
                $this->rotatingObject->getAngle()->plus($this->rotatingObject->getAngularVelocity())
            );
        } catch (Throwable $exception) {
            throw new CommandException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}

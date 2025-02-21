<?php

namespace App\Homework4;

use App\Homework3\ICommand;

class ChangeVelocityCommand implements ICommand
{
    public const string ZERO_MODULE_ERR_MSG = 'Невозможно изменить нулевую скорость.';
    private IVelocityChangeable $velocityChangeable;

    public function __construct(IVelocityChangeable $velocityChangeable)
    {
        $this->velocityChangeable = $velocityChangeable;
    }

    /**
     * @throws CommandException
     */
    public function execute(): void
    {
        $velocity = $this->velocityChangeable->getVelocity();
        if ($velocity->getModule() === 0) {
            throw new CommandException(self::ZERO_MODULE_ERR_MSG);
        }

        $this->velocityChangeable->setVelocity(
            new Velocity($velocity->getModule(), $velocity->getAngle()->plus($this->velocityChangeable->getAngle()))
        );
    }
}

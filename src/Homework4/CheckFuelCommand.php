<?php

namespace App\Homework4;

use App\Homework3\ICommand;

class CheckFuelCommand implements ICommand
{
    private IFuelObject $fuel;

    public function __construct(IFuelObject $fuel)
    {
        $this->fuel = $fuel;
    }

    /**
     * @throws CommandException
     */
    public function execute(): void
    {
        $newVolume = $this->fuel->getFuelVolume() - $this->fuel->getOneTimeVolume();
        if ($newVolume < 0) {
            throw new CommandException('Недостаточно топлива!');
        }
    }
}

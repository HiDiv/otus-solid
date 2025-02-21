<?php

namespace App\Homework4;

class BurnFuelCommand
{
    private IFuelObject $fuel;

    public function __construct(IFuelObject $fuel)
    {
        $this->fuel = $fuel;
    }

    public function execute(): void
    {
        $this->fuel->setFuelVolume($this->fuel->getFuelVolume() - $this->fuel->getOneTimeVolume());
    }

}

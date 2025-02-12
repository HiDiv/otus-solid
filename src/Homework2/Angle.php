<?php

namespace App\Homework2;

use Exception;

class Angle
{
    /** @var int Угол в количестве "делений" */
    private int $d;
    /** @var int Количество "делений" в полной окружности */
    private int $n;

    /**
     * @throws Exception
     */
    public function __construct(int $d, int $n)
    {
        if ($d >= $n) {
            throw new Exception('d must be less than n');
        }

        $this->d = $d;
        $this->n = $n;
    }

    /**
     * @throws Exception
     */
    public function plus(Angle $angle): Angle
    {
        if ($this->getN() !== $angle->getN()) {
            throw new Exception('Operands Angle must have same division');
        }

        $newD = $this->getD() + $angle->getD();
        while ($newD >= $this->getN()) {
            $newD -= $this->getN();
        }

        return new Angle($newD, $this->getN());
    }

    public function getN(): int
    {
        return $this->n;
    }

    public function getD(): int
    {
        return $this->d;
    }
}

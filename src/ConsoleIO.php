<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

class ConsoleIO
{
    private $inputStream;
    private $outputStream;

    public function __construct($inputStream = STDIN, $outputStream = STDOUT)
    {
        $this->inputStream = $inputStream;
        $this->outputStream = $outputStream;
    }

    public function output(string $message): void
    {
        fwrite($this->outputStream, $message);
    }

    /**
     * @throws RuntimeException Если введенное значение не является числом.
     */
    public function inputFloat(string $prompt): float
    {
        $this->output($prompt);
        $input = trim(fgets($this->inputStream));

        if (!is_numeric($input)) {
            throw new RuntimeException("Ошибка: введенное значение не является числом.");
        }

        return (float) $input;
    }
}

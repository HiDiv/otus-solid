<?php

namespace Tests\Unit;

use App\ConsoleIO;
use Codeception\Test\Unit;
use RuntimeException;
use Tests\Support\UnitTester;

class AppConsoleIOTest extends Unit
{
    protected UnitTester $tester;

    public function testOutput(): void
    {
        $outputStream = fopen('php://memory', 'r+');
        $consoleIO = new ConsoleIO(STDIN, $outputStream);

        $consoleIO->output("Hello, World!");

        rewind($outputStream);
        $output = stream_get_contents($outputStream);
        fclose($outputStream);

        verify($output)->equals("Hello, World!");
    }

    public function testInputFloatValid(): void
    {
        $inputStream = fopen('php://memory', 'r+');
        fwrite($inputStream, "42.5\n");
        rewind($inputStream);

        $consoleIO = new ConsoleIO($inputStream, STDOUT);
        $result = $consoleIO->inputFloat("Enter a number:");

        fclose($inputStream);

        verify($result)->equals(42.5);
    }

    public function testInputFloatInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Ошибка: введенное значение не является числом.");

        $inputStream = fopen('php://memory', 'r+');
        fwrite($inputStream, "not a number\n");
        rewind($inputStream);

        $consoleIO = new ConsoleIO($inputStream, STDOUT);
        $consoleIO->inputFloat("Enter a number:");

        fclose($inputStream);
    }
}

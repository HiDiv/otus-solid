<?php

namespace App\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\CommandProcessingError;

class CommandProcessorService implements CommandProcessorInterface
{
    /**
     * @throws CommandProcessingError
     */
    public function process(User $user, Game $game, array $commandParams): void
    {
        // Простейшая валидация: если нет никаких параметров
        if (empty($commandParams)) {
            throw new CommandProcessingError('Command parameters cannot be empty.');
        }
        // Здесь вы можете добавить любую бизнес‐логику
    }
}

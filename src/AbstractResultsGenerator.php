<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\ResultsGeneratorInterface;

/**
 * @template Game of GameInterface
 * @implements ResultsGeneratorInterface<Game>
 */
abstract class AbstractResultsGenerator implements ResultsGeneratorInterface
{
    public function generateToFile(GameInterface $game, string $filename) : bool {
        return file_put_contents($filename, $this->generate($game)) !== false;
    }

    abstract public function generate(GameInterface $game) : string;
}
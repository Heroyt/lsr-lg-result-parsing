<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\GameInterface;

/**
 * @template Game of GameInterface
 */
interface ResultsGeneratorInterface
{

    /**
     * Check if the generator can handle the given game
     * @param Game $game
     * @return bool
     */
    public static function checkGame(GameInterface $game) : bool;

    /**
     * Generate results file content
     * @param Game $game
     * @return string
     */
    public function generate(GameInterface $game) : string;

    /**
     * Generate results file and save it to given filename
     * @param Game $game
     * @param string $filename
     * @return bool
     */
    public function generateToFile(GameInterface $game, string $filename) : bool;

}
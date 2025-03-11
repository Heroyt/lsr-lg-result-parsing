<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\GameInterface;

interface ResultsGeneratorInterface
{

    public static function checkGame(GameInterface $game) : bool;

    public function generate(GameInterface $game) : string;

    public function generateToFile(GameInterface $game, string $filename) : bool;

}
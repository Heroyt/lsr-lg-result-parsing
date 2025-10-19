<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface ModifyScoresMode extends GameModeInterface
{
    /**
     * @template Game of GameInterface
     * @param Game $game
     * @return void
     */
    public function modifyResults(GameInterface $game) : void;
}
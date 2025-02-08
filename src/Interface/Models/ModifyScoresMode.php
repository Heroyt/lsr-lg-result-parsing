<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface ModifyScoresMode extends GameModeInterface
{
    public function modifyResults(GameInterface $game) : void;
}
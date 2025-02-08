<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Interface\Models\GameInterface;

/**
 * @template G of GameInterface
 */
interface WithGameInterface
{

    /** @var G */
    public GameInterface $game {
        get;
        set;
    }

    /**
     * @return G
     */
    public function loadGame() : GameInterface;

    /**
     * @param  G  $game
     * @return $this
     */
    public function setGame(GameInterface $game) : static;

    public function saveGame() : bool;
}
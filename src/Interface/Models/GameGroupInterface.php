<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;


interface GameGroupInterface extends ModelInterface
{

    public string $name {
        get;
        set;
    }

    public bool $active {
        get;
        set;
    }

    public function getPlayerByName(string $name): ?GroupPlayerInterface;

    /**
     * @template Player of PlayerInterface
     * @param Player $player
     * @return GroupPlayerInterface|null
     */
    public function getPlayer(PlayerInterface $player): ?GroupPlayerInterface;

    /**
     * @return string[]
     */
    public function getGamesCodes(): array;

    /**
     * Gets formatted date range for this group
     *
     * @param string $format How to format the dates
     *
     * @return string
     */
    public function getDateRange(string $format = 'd.m.Y'): string;

}
<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Enums;

/**
 * Types of game modes
 *
 * @method static GameModeType|null tryFrom(string $value)
 * @method static GameModeType from(mixed $value)
 * @property string $value
 */
enum GameModeType : string
{
    case TEAM = 'TEAM';
    case SOLO = 'SOLO';
}

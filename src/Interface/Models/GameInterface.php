<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use DateTimeInterface;
use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Interface\WithMetaInterface;
use Lsr\Lg\Results\Interface\WithPlayersInterface;
use Lsr\Lg\Results\Interface\WithTeamsInterface;
use Lsr\Lg\Results\Timing;

/**
 * @template T of TeamInterface
 * @template P of PlayerInterface
 * @template M of array<string,mixed>
 * @extends WithTeamsInterface<T>
 * @extends WithPlayersInterface<P>
 * @extends WithMetaInterface<M>
 */
interface GameInterface extends WithMetaInterface, WithPlayersInterface, WithTeamsInterface, ModelInterface
{
    public ?string $resultsFile {
        get;
        set;
    }
    public string $modeName {
        get;
        set;
    }
    public ?DateTimeInterface $fileTime {
        get;
        set;
    }
    public ?DateTimeInterface $start {
        get;
        set;
    }
    public ?DateTimeInterface $importTime {
        get;
        set;
    }
    public ?DateTimeInterface $end {
        get;
        set;
    }
    public ?Timing $timing {
        get;
        set;
    }
    public string $code {
        get;
        set;
    }
    public ?GameModeInterface $mode {
        get;
        set;
    }
    public GameModeType $gameType {
        get;
        set;
    }
    public ?MusicModeInterface $music {
        get;
        set;
    }
    public ?GameGroupInterface $group {
        get;
        set;
    }

    /**
     * Check if game is already finished based on current time
     *
     * @return bool
     * @phpstan-assert-if-true !null $this->start
     * @phpstan-assert-if-true !null $this->end
     * @phpstan-assert-if-true !null $this->importTime
     */
    public function isFinished(): bool;

    /**
     * Check if game was already started based on current time
     *
     * @return bool
     * @phpstan-assert-if-true !null $this->start
     */
    public function isStarted(): bool;
}

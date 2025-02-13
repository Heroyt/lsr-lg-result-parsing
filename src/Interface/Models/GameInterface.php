<?php

declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

use DateTimeInterface;
use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Interface\WithMetaInterface;
use Lsr\Lg\Results\Interface\WithPlayersInterface;
use Lsr\Lg\Results\Interface\WithTeamsInterface;
use Lsr\Lg\Results\Timing;

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
    public bool $started {
        get;
        set;
    }
    public bool $finished {
        get;
        set;
    }
}

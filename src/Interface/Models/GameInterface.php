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
    }
    public string $modeName {
        get;
    }
    public ?DateTimeInterface $fileTime {
        get;
    }
    public ?DateTimeInterface $start {
        get;
    }
    public ?DateTimeInterface $importTime {
        get;
    }
    public ?DateTimeInterface $end {
        get;
    }
    public ?Timing $timing {
        get;
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

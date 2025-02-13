<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface\Models;

interface MusicModeInterface extends ModelInterface
{

    public string $name {
        get;
        set;
    }

    public ?string $group {
        get;
        set;
    }

    public ?string $backgroundImage {
        get;
        set;
    }

    public ?string $icon {
        get;
        set;
    }

}
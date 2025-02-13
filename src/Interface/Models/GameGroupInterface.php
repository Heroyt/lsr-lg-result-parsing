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

}
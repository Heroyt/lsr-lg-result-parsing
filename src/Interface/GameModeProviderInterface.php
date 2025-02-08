<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\Interface;

use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Interface\Models\GameModeInterface;

interface GameModeProviderInterface
{

    public function find(string       $name,
                         GameModeType $type = GameModeType::TEAM,
                         string       $system = ''
    ) : ?GameModeInterface;

}
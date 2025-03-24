<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx;

enum AccuracyBonus : int
{

    case OFF       = 0;
    case FACTOR    = 1;
    case THRESHOLD = 2;

}

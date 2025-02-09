<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

enum GameStyleType : int
{

    case SOLO                = 0;
    case TEAM                = 1;
    case TEAM_CAPTURE        = 2;
    case ZOMBIES_SOLO        = 3;
    case ZOMBIES_TEAM        = 4;
    case VIP                 = 5;
    case CROSSFIRE           = 6;
    case SENSORTAG_SOLO      = 7;
    case SENSORTAG_TEAM      = 8;
    case ROCK_PAPER_SCISSORS = 9;
    case PARALLEL            = 10;

}

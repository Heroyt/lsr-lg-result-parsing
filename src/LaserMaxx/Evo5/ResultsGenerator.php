<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo5;

use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Timing;

/**
 * @template-covariant Team of Evo5TeamInterface
 * @template-covariant Player of Evo5PlayerInterface
 * @template-covariant Meta of array{hash?:string,mode?:string,loadTime?:numeric}|array<string,mixed>
 * @template Game of Evo5GameInterface<Team, Player, Meta>
 * @extends \Lsr\Lg\Results\LaserMaxx\ResultsGenerator<Team, Player, Meta, Game>
 */
class ResultsGenerator extends \Lsr\Lg\Results\LaserMaxx\ResultsGenerator
{

    /**
     * @template G of GameInterface
     * @param G $game
     * @return bool
     * @phpstan-assert-if-true Game $game
     */
    public static function checkGame(GameInterface $game) : bool {
        return $game instanceof Evo5GameInterface;
    }

    /**
     * @param Game $game
     * @return string
     * @throws \JsonException
     */
    public function generate(GameInterface $game) : string {
        return
            $this->getHeader($game)."\n".
            $this->getGroup($game)."\n".
            $this->getPack($game)."\n".
            $this->getTeam($game)."\n".
            $this->getPackx($game)."\n".
            $this->getPacky($game)."\n".
            $this->getTeamx($game)."\n".
            $this->getHits($game)."\nGAMECLONES{0}#\n";
    }

    /**
     * @param Game $game
     * @return string
     */
    public function getHeader(Evo5GameInterface $game) : string {
        $fileNum = $game->fileNumber;
        $timing = $game->timing ?? new Timing(20, 15, 10);
        assert($game->start !== null);
        $start = $game->start->format('YmdHis');
        assert($game->end !== null);
        $end = $game->end->format('YmdHis');
        $gameType = match ($game->gameType) {
            GameModeType::SOLO => 0,
            GameModeType::TEAM => 1,
        };
        $antiStalking = $game->antiStalking ? 1 : 0;
        $allowFriendlyFire = $game->allowFriendlyFire ? 1 : 0;
        return <<<LMXGAME
        SITE{53874,0504011,EVO-5 MAXX}#
        GAME{{$fileNum},,{$start},{$end},{$game->playerCount}}#
        TIMING{{$timing->before},{$timing->gameLength},{$timing->after},{$start},{$end},{$end}}#
        STYLE{{$game->modeName},,{$gameType},{$timing->gameLength},0}#
        STYLEX{{$game->respawn},{$game->ammo},{$game->lives},0,0}#
        STYLELEDS{0,15,15,15,15,15,1,5,1,0,15}#
        STYLEFLAGS{0,0,1,1,0,{$antiStalking},0,{$allowFriendlyFire},0,1,0,1,0,1,1,1,1,1,0,0,0,1,0,0,1,0,0}#
        STYLESOUNDS{255}#
        SCORING{{$game->scoring->deathOther},{$game->scoring->hitOther},{$game->scoring->deathOwn},{$game->scoring->hitOwn},{$game->scoring->hitPod},{$game->scoring->shot},{$game->scoring->machineGun},{$game->scoring->invisibility},{$game->scoring->agent},{$game->scoring->shield},0,0,0,0,0,0}#
        ENVIRONMENT{,,,,,C:\LaserMaxx\shared\music\evo5.mp3,,,,,}#
        VIPSTYLE{1,4,999,1,1,0,100}#
        VAMPIRESTYLE{0,1,0,3,99,0}#
        SWITCHSTYLE{0,0}#
        ASSISTEDSTYLE{0,0,0,0,0,0,0,0}#
        HITSTREAKSTYLE{0,5,15}#
        SHOWDOWNSTYLE{0,3,1,3,2}#
        ACTIVITYSTYLE{0,0,0}#
        TERMINATESTYLE{0}#
        MINESTYLE{0,0,0,0,Unnamed Unit}#
        MINESTYLE{1,0,0,0,Unnamed Unit}#
        MINESTYLE{2,0,0,0,Unnamed Unit}#
        MINESTYLE{3,0,0,0,Unnamed Unit}#
        MINESTYLE{4,0,0,0,Unnamed Unit}#
        MINESTYLE{5,0,0,0,Unnamed Unit}#
        MINESTYLE{6,0,20,6,Mina}#
        MINESTYLE{7,0,20,6,Mina}#
        LMXGAME;
    }

    /**
     * @param Game $game
     * @return string
     */
    public function getPack(Evo5GameInterface $game) : string {
        $players = [];
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACK{%s,%s,%d,0,%d,0,0}#',
                $player->vest,
                $this->escapeName($player->name),
                $player->color,
                $player->vip ? 1 : 0,
            );
        }
        return implode("\n", $players);
    }

    /**
     * @param Game $game
     * @return string
     */
    public function getPackx(Evo5GameInterface $game) : string {
        $players = [];
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACKX{%s,%d,%d,%d,%d,%d,%s,0}#',
                $player->vest,
                $player->score,
                $player->shots,
                $player->hits,
                $player->deaths,
                $player->position,
                $player->myLasermaxx,
            );
        }
        return implode("\n", $players);
    }

    /**
     * @param Game $game
     * @return string
     */
    public function getPacky(Evo5GameInterface $game) : string {
        $players = [];
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACKY{%s,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,0,%d,0,0,0,0}#',
                $player->vest,
                $player->shotPoints,
                $player->scoreBonus,
                $player->scorePowers,
                $player->scoreMines,
                $player->ammoRest,
                $player->accuracy,
                $player->minesHits,
                $player->bonus->agent,
                $player->bonus->invisibility,
                $player->bonus->machineGun,
                $player->bonus->shield,
                $player->hitsOther,
                $player->hitsOwn,
                $player->deathsOther,
                $player->deathsOwn,
                $player->getRemainingLives(),
                $player->hitsOther * $game->scoring->hitOther,
            );
        }
        return implode("\n", $players);
    }

    /**
     * @param Game $game
     * @return string
     */
    public function getTeamx(Evo5GameInterface $game) : string {
        $teams = [];
        foreach ($game->teams as $team) {
            $teams[] = sprintf(
                'TEAMX{%d,%d,%d}#',
                $team->color,
                $team->score,
                $team->position,
            );
        }
        return implode("\n", $teams);
    }
}
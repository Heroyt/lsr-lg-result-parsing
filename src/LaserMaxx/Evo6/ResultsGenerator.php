<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Timing;

class ResultsGenerator extends \Lsr\Lg\Results\LaserMaxx\ResultsGenerator
{

    public static function checkGame(GameInterface $game) : bool {
        return $game instanceof Evo6GameInterface;
    }

    public function generate(GameInterface $game) : string {
        assert($game instanceof Evo6GameInterface);
        return
            $this->getHeader($game)."\n".
            $this->getGroup($game)."\n".
            $this->getPack($game)."\n".
            $this->getTeam($game)."\n".
            $this->getPackx($game)."\n".
            $this->getPacky($game)."\n".
            $this->getPackz($game)."\n".
            $this->getTeamx($game)."\n".
            $this->getHits($game)."\nGAMECLONES{0}#\n";
    }

    public function getHeader(Evo6GameInterface $game) : string {
        $fileNum = $game->fileNumber ?? '12345';
        $timing = $game->timing ?? new Timing(20, 15, 10);
        $start = $game->start->format('YmdHis');
        $end = $game->end->format('YmdHis');
        $gameType = match ($game->gameType) {
            GameModeType::SOLO => 0,
            GameModeType::TEAM => 1,
        };
        $antiStalking = $game->antiStalking ? 1 : 0;
        $blastShots = $game->blastShots ? 1 : 0;
        $allowFriendlyFire = $game->allowFriendlyFire ? 1 : 0;
        $reloading = $game->reloadClips > 0 ? 42 : 0;
        $ammoClips = ($game->ammo << 8) + $game->reloadClips;

        return <<<LMXGAME
        SITE{53874,0504011,EVO-6 MAXX}#
        GAME{{$fileNum},,{$start},{$end},{$game->playerCount}}#
        TIMING{{$timing->before},{$timing->gameLength},{$timing->after},{$start},{$end},{$end}}#
        STYLE{{$game->modeName},,{$gameType},{$timing->gameLength},0,{$game->gameStyleType->value},0}#
        STYLEX{{$game->respawn},{$game->ammo},{$game->lives}}#
        STYLELEDS{0,15,15,15,15,15,1,5,1,0,15}#
        STYLEFLAGS{0,0,1,1,0,{$antiStalking},{$blastShots},{$allowFriendlyFire},0,1,0,1,0,1,1,1,1,1,0,0,0,1,0,0,1,0,{$reloading},{$ammoClips},{$game->triggerSpeed->value},0}#
        STYLESOUNDS{255}#
        SCORING{{$game->scoring->deathOther},{$game->scoring->hitOther},{$game->scoring->deathOwn},{$game->scoring->hitOwn},{$game->scoring->hitPod},{$game->scoring->shot},{$game->scoring->machineGun},{$game->scoring->invisibility},{$game->scoring->agent},{$game->scoring->shield},0,{$game->scoring->accuracyBonus->value},{$game->scoring->accuracyThreshold},{$game->scoring->accuracyThresholdBonus},{$game->scoring->encouragementBonus->value},{$game->scoring->encouragementBonusScore},{$game->scoring->power},{$game->scoring->penalty}}#
        ENVIRONMENT{,,,,,C:\LaserMaxx\shared\music\evo6.mp3,,,,,}#
        REALITY{0}#
        {$this->getVipStyle($game)}
        {$this->getZombieStyle($game)}
        SWITCHSTYLE{0,0}#
        ASSISTEDSTYLE{0,0,0,0,0,0,0,0}#
        HITSTREAKSTYLE{0,5,15}#
        SHOWDOWNSTYLE{0,3,1,3,2}#
        ACTIVITYSTYLE{0,{$game->scoring->activity},0}#
        KNOCKOUTSTYLE{0,{$game->scoring->knockout}}#
        HITGAINSTYLE{{$game->hitGainSettings->ammo},{$game->hitGainSettings->lives},0}#
        CROSSFIRESTYLE{0}#
        PARALLELSTYLE{0}#
        SENSORTAGSTYLE{0}#
        ROCKPAPERSCISSORSSTYLE{0}#
        RESPAWNSTYLE{0,{$game->respawnSettings->respawnLives},30}#
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

    public function getVipStyle(Evo6GameInterface $game) : string {
        $vipOn = $game->gameStyleType === GameStyleType::VIP ? 1 : 0;
        $vipBazooka = $game->vipSettings->hitType === HitType::BAZOOKA ? 1 : 0;
        $vipDouble = $game->vipSettings->hitType === HitType::DOUBLE ? 1 : 0;
        $vipTenfold = $game->vipSettings->hitType === HitType::TENFOLD ? 1 : 0;
        $vipIgnoreTeammateHits = $game->vipSettings->ignoreTeammateHits ? 1 : 0;
        $vipBlastShots = $game->vipSettings->blastShots ? 1 : 0;
        $vipChangeRespawn = $game->vipSettings->respawn !== $game->respawn ? 1 : 0;
        return <<<LMXGAME
            VIPSTYLE{{$vipOn},{$game->vipSettings->lives},{$game->vipSettings->ammo}},{$vipBazooka},0,{$game->vipSettings->killTeam},{$game->vipSettings->vipHitScore},1,{$vipDouble},{$vipTenfold},{$vipIgnoreTeammateHits},0,{$vipChangeRespawn},{$game->vipSettings->respawn},{$vipBlastShots},0,0,0}#
            LMXGAME;
    }

    public function getZombieStyle(Evo6GameInterface $game) : string {
        $zombiesOn = ($game->gameStyleType === GameStyleType::ZOMBIES_TEAM || $game->gameStyleType === GameStyleType::ZOMBIES_SOLO) ?
            1 : 0;
        $zombiesSpecial = $game->zombieSettings->zombieSpecial ? 1 : 0;
        $infectHits = $game->zombieSettings->infectHits - 1;
        return <<<LMXGAME
            VAMPIRESTYLE{{$zombiesOn},{$zombiesSpecial},{$game->zombieSettings->zombieTeamNumber},{$game->zombieSettings->lives},{$game->zombieSettings->ammo},{$infectHits},0}#
            LMXGAME;

    }

    public function getPack(Evo6GameInterface $game) : string {
        $players = [];
        /** @var Evo6PlayerInterface $player */
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACK{%s,%s,%d,0,%d,0,0,%d}#',
                $player->vest,
                $this->escapeName($player->name),
                $player->color,
                $player->vip ? 1 : 0,
                $player->birthday ? 1 : 0,
            );
        }
        return implode("\n", $players);
    }

    public function getPackx(Evo6GameInterface $game) : string {
        $players = [];
        /** @var Evo6PlayerInterface $player */
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACKX{%s,%d,%d,%d,%d,%d,%s,%d,%d}#',
                $player->vest,
                $player->score,
                $player->shots,
                $player->hits,
                $player->deaths,
                $player->position,
                $player->myLasermaxx,
                $player->activity,
                $player->calories,
            );
        }
        return implode("\n", $players);
    }

    public function getPacky(Evo6GameInterface $game) : string {
        $players = [];
        /** @var Evo6PlayerInterface $player */
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACKY{%s,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,0,%d,%d,%d,%d,0,%d,%d,%d,%d,%d}#',
                $player->vest,
                $player->shotPoints,
                $player->scoreBonus,
                $player->scorePowers,
                $player->scoreMines,
                $player->ammoRest,
                $player->accuracy,
                $player->minesHits,
                0, // Agent
                0, // Invisibility
                0, // Blast shots
                0, // Shield
                $player->hitsOther,
                $player->hitsOwn,
                $player->deathsOther,
                $player->deathsOwn,
                $player->getRemainingLives(),
                ($player->hitsOther * $game->scoring->hitOther) + ($player->deathsOther * $game->scoring->deathOther),
                $player->scoreVip,
                $player->scoreActivity,
                $player->scoreEncouragement,
                $player->scoreKnockout,
                $player->scoreReality,
                $player->getBonusCount(),
                $player->penaltyCount,
                $player->scorePenalty,
            );
        }
        return implode("\n", $players);
    }

    public function getPackz(Evo6GameInterface $game) : string {
        $players = [];
        /** @var Evo6PlayerInterface $player */
        foreach ($game->players as $player) {
            $players[] = sprintf(
                'PACKZ{%s,%d,%d}#',
                $player->vest,
                $player->deathsOther,
                $player->deathsOwn,
            );
        }
        return implode("\n", $players);
    }

    public function getTeamx(Evo6GameInterface $game) : string {
        $teams = [];
        /** @var Evo6TeamInterface $team */
        foreach ($game->teams as $team) {
            $teams[] = sprintf(
                'TEAMX{%d,%d,%d,0}#',
                $team->color,
                $team->score,
                $team->position,
            );
        }
        return implode("\n", $teams);
    }
}
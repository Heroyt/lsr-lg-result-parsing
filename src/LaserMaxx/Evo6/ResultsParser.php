<?php
declare(strict_types=1);

namespace Lsr\Lg\Results\LaserMaxx\Evo6;

use DateTime;
use JsonException;
use Lsr\Lg\Results\AbstractResultsParser;
use Lsr\Lg\Results\Enums\GameModeType;
use Lsr\Lg\Results\Exception\ResultsParseException;
use Lsr\Lg\Results\Interface\Models\ModifyScoresMode;
use Lsr\Lg\Results\Timing;
use Lsr\Lg\Results\WithMetadata;
use Lsr\Logging\Exceptions\DirectoryCreationException;
use Lsr\Logging\Logger;
use Lsr\ObjectValidation\Exceptions\ValidationException;


/**
 * Result parser for the EVO6 system
 *
 * @extends AbstractResultsParser<Evo6GameInterface>
 */
abstract class ResultsParser extends AbstractResultsParser
{
    use WithMetadata;

    public const string REGEXP = '/([A-Z]+){([^{}]*)}#/';

    /** @var string Default LMX date string passed when no distinct date should be used (= null) */
    public const string EMPTY_DATE = '20000101000000';

    public const string SYSTEM = 'Evo6';

    /**
     * @inheritDoc
     */
    public static function getFileGlob() : string {
        return '*.game';
    }

    /**
     * @inheritDoc
     */
    public static function checkFile(string $fileName = '', string $contents = '') : bool {
        if (empty($fileName) && empty($contents)) {
            return false;
        }

        if (empty($contents)) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            if ($extension !== 'game') {
                return false;
            }

            $contents = file_get_contents($fileName);
        }
        if (!$contents) {
            return false;
        }
        return (bool) preg_match('/SITE{.*EVO-6 MAXX}#/', $contents);
    }

    /**
     * Parse a game results file and return a parsed object
     *
     * @return Evo6GameInterface
     * @throws DirectoryCreationException
     * @throws ValidationException
     * @throws ResultsParseException
     * @throws JsonException
     * @noinspection PhpDuplicateSwitchCaseBodyInspection
     */
    public function parse() : Evo6GameInterface {
        /** @var Evo6GameInterface $game */
        $game = new ($this->gameClass);

        // Results file info
        $pathInfo = pathinfo($this->fileName);
        preg_match('/(\d+)/', $pathInfo['filename'], $matches);
        $game->resultsFile = $pathInfo['filename'];
        $game->fileNumber = (int) ($matches[0] ?? 0);
        $fTime = filemtime($this->fileName);
        if (is_int($fTime)) {
            $game->fileTime = new DateTime();
            $game->fileTime->setTimestamp($fTime);
        }

        // Parse file into lines and arguments
        [, $titles, $argsAll] = $this->matchAll($this::REGEXP);

        // Check if parsing is successful and lines were found
        if (empty($titles) || empty($argsAll)) {
            throw new ResultsParseException('The results file cannot be parsed: '.$this->fileName);
        }

        /** @var array<string,string> $meta Meta data from game */
        $meta = [];

        $keysVests = [];
        $currKey = 1;
        $now = new DateTime();
        foreach ($titles as $key => $title) {
            $args = $this->getArgs($argsAll[$key]);

            // To prevent calling the count() function multiple times - save the value
            $argsCount = count($args);

            switch ($title) {
                // SITE line contains information about the LMX arena and possibly version?
                // This can only be useful to validate if the results are from the correct system (EVO-5)
                case 'SITE':
                    if ($args[2] !== 'EVO-6 MAXX') {
                        throw new ResultsParseException(
                            'Invalid results system type. - '.$title.': '.json_encode($args, JSON_THROW_ON_ERROR)
                        );
                    }
                    break;

                // GAME contains general game information
                // [0] game number
                // [1] group name
                // [2] Start datetime (when the "Start game" button was pressed)
                // [3] Finish datetime (when the results are downloaded)
                // [4] Player count.
                case 'GAME':
                    if ($argsCount !== 5) {
                        throw new ResultsParseException('Invalid argument count in GAME');
                    }
                    [$gameNumber, , $dateStart, $dateEnd] = $args;
                    $game->fileNumber = (int) $gameNumber;
                    if ($dateStart !== $this::EMPTY_DATE) {
                        $date = DateTime::createFromFormat('YmdHis', $dateStart);
                        if ($date === false) {
                            $date = null;
                        }
                        $game->start = $date;
                        $game->started = $now > $game->start;
                    }
                    if ($dateEnd !== $this::EMPTY_DATE) {
                        $date = DateTime::createFromFormat('YmdHis', $dateEnd);
                        if ($date === false) {
                            $date = null;
                        }
                        $game->importTime = $date;
                    }
                    break;

                // TIMING contains all game settings regarding game times
                // [0] Start time [s]
                // [1] Play time [min]
                // [2] End time [s]
                // [3] Play start time [datetime]
                // [4] Play end time [datetime]
                // [5] End time [datetime] (Real end - after the play ended and after end time)
                case 'TIMING':
                    if ($argsCount !== 6 && $argsCount !== 5) {
                        throw new ResultsParseException('Invalid argument count in TIMING');
                    }
                    $game->timing = new Timing(
                        before    : (int) $args[0],
                        gameLength: (int) $args[1],
                        after     : (int) $args[2]
                    );
                    $dateStart = $args[3];
                    if ($dateStart !== $this::EMPTY_DATE) {
                        $date = DateTime::createFromFormat('YmdHis', $dateStart);
                        if ($date === false) {
                            $date = null;
                        }
                        $game->start = $date;
                    }
                    $dateEnd = $args[4];
                    if ($dateEnd !== $this::EMPTY_DATE) {
                        $date = DateTime::createFromFormat('YmdHis', $dateEnd);
                        if ($date === false) {
                            $date = null;
                        }
                        $game->end = $date;
                        $game->finished = $now->getTimestamp() > ($game->end?->getTimestamp() + $game->timing->after);
                    }
                    break;

                // STYLE contains game mode information
                // [0] Game mode's name
                // [1] Game mode's description
                // [2] Team (1) / Solo (0) game type
                // [3] Play length [min]
                // [4] Locked
                // [5] Game type - Solo (0), Team (1), Team capture (2), Zombies (Solo = 3, Team = 4), VIP (5), Crossfire (6), Sensortag (Solo = 7, Team = 8), Rock-Paper-Scissors (9), Parallel (10)
                // [6] Do NOT print scorecards.
                case 'STYLE':
                    if ($argsCount !== 7) {
                        throw new ResultsParseException('Invalid argument count in STYLE');
                    }
                    $game->modeName = $args[0];
                    $type = ((int) $args[2]) === 1 ? GameModeType::TEAM : GameModeType::SOLO;
                    $game->mode = $this->gameModeProvider->find($args[0], $type, self::SYSTEM);
                    $game->gameType = $type;
                    $game->gameStyleType = GameStyleType::tryFrom((int) $args[5]) ?? ($type === GameModeType::TEAM ?
                        GameStyleType::TEAM : GameStyleType::SOLO);
                    break;

                // STYLEX contains additional game mode settings
                // [0] Respawn time [s]
                // [1] Starting ammo
                // [2] Starting lives
                case 'STYLEX':
                    if ($argsCount < 3) {
                        throw new ResultsParseException('Invalid argument count in STYLE');
                    }
                    $game->respawn = (int) $args[0];
                    $game->ammo = (int) $args[1];
                    $game->lives = (int) $args[2];
                    break;

                // STYLELEDS contains lightning settings
                // [0] ???
                // [1] ???
                // [2] ???
                // [3] ???
                // [4] ???
                // [5] ???
                // [6] Armed LEDs
                // [7] Start LEDs
                // [8] Play LEDs?
                // [9] Hit LEDs?
                // [10] Game over LEDs?
                case 'STYLELEDS':
                    break;
                // STYLEFLAGS
                // [0] Force lasers off
                // [1] Two trigger shooting
                // [2] Pack sounds
                // [3] Voice coach
                // [4] Vibrations
                // [5] Anti-stalking
                // [6] Always blast shots
                // [7] Allow hits by teammates
                // [8] Do NOT show originator of hits
                // [9] Flash when hit
                // [10] Double laser blast shots
                // [11] Show remaining play time
                // [12] SWAT laser?
                // [13] SWAT light
                // [14] Front sensor
                // [15] Gun sensor
                // [16] Back sensor
                // [17] Shoulder sensor
                // [18] ???
                // [19] ???
                // [20] Display on during game
                // [21] Dimmed LEDs in game
                // [22] ???
                // [23] Dimmed LEDs when armed
                // [24] ???
                // [25] Show special packs when armed
                // [26] Ammo clips (0 = off, 41 = reload after 5 seconds, 42 = reload after 5 trigger pulls, 3 = reload after pressing chest button)
                // [27] Ammo clips / shots (binary concat: first byte = clips, second byte = shots in clip)
                // [28] Trigger speed (0 = fast, 1 = slow, 2 = very slow)
                // [29] Sound of special shots (0 = normal, 105 = shotgun, 107 = rifle, 111 = pistol)
                case 'STYLEFLAGS':
                    $game->antiStalking = ((int) ($args[5] ?? 0)) !== 0;
                    $game->blastShots = ((int) ($args[6] ?? 0)) !== 0;
                    $game->allowFriendlyFire = ((int) ($args[7] ?? 0)) !== 0;
                    $reloading = ((int) ($args[26] ?? 0)) !== 0;
                    if ($reloading) {
                        $clips = (int) ($args[27] ?? 0);
                        $game->ammo = $clips & 0xFF;      // First byte (LSB)
                        $game->reloadClips = $clips >> 8; // Second byte (MSB)
                    }
                    $game->triggerSpeed = TriggerSpeed::tryFrom((int) ($args[28] ?? 0)) ?? TriggerSpeed::FAST;
                    break;
                // STYLESOUNDS
                // [0] Sample table: default (255), LaserMaxx (0), Unused (1), LaserTrooper (2), unused (3), Try this one! (4)
                case 'STYLESOUNDS':
                    break;
                // SCORING contains score settings
                // [0] Death enemy
                // [1] Hit enemy
                // [2] Death teammate
                // [3] Hit teammate
                // [4] Death from pod
                // [5] Score per shot
                // [6] ??? (Machine gun)
                // [7] ??? (Invisibility)
                // [8] ??? (Agent)
                // [9] ??? (Shield)
                // [10] Highscore
                // [11] Accuracy bonus (0 = off, 1 = Accuracy factor, 2 = Accuracy threshold)
                // [12] Accuracy threshold
                // [13] Accuracy threshold bonus
                // [14] Encouragement bonus (0 = off, 1 = Float to 100, 2 = all players get bonus)
                // [15] Encouragement bonus for all
                // [16] Points for power
                // [17] Points for penalty
                case 'SCORING':
                    if ($argsCount !== 18) {
                        throw new ResultsParseException('Invalid argument count in SCORING');
                    }
                    $game->scoring = new Scoring(
                        deathOther             : (int) $args[0],
                        hitOther               : (int) $args[1],
                        deathOwn               : (int) $args[2],
                        hitOwn                 : (int) $args[3],
                        hitPod                 : (int) $args[4],
                        shot                   : (int) $args[5],
                        highscore              : (int) $args[10],
                        accuracyBonus          : AccuracyBonus::tryFrom((int) ($args[11] ?? 0)) ?? AccuracyBonus::OFF,
                        accuracyThreshold      : (int) $args[12],
                        accuracyThresholdBonus : (int) $args[13],
                        encouragementBonus     : EncouragementBonus::tryFrom(
                        (int) ($args[14] ?? 0)
                    ) ?? EncouragementBonus::OFF,
                        encouragementBonusScore: (int) $args[15],
                        power                  : (int) $args[16],
                        penalty                : (int) $args[17]
                    );
                    break;

                // ENVIRONMENT contains sound and effects settings
                // [0] ???
                // [1] ???
                // [2] ???
                // [3] Armed music file
                // [4] Intro music file
                // [5] Play music file
                // [4] Game over music file
                case 'ENVIRONMENT':
                    // REALITY
                    // [0] Reality preset
                case 'REALITY':
                break;
                // VIPSTYLE contains special mode settings
                // [0] On/Off - compatibility???
                // [1] VIP lives
                // [2] VIP ammo
                // [3] Bazooka on/off
                // [4] VIP LEDs
                // [5] When a VIP is killed, kill the whole team
                // [6] Points for VIP hit
                // [7] End game when (0 = All VIPs killed, 1 = One VIP remaining)
                // [8] Double hits on/off
                // [9] Tenfold hits on/off
                // [10] Ignore hits from teammates
                // [11] ???
                // [12] Change VIP respawn
                // [13] VIP respawn
                // [14] Always blast shots
                // [15] Silent messages
                // [16] ???
                // [17] ???
                case 'VIPSTYLE':
                    if ($argsCount < 15) {
                        throw new ResultsParseException('Invalid argument count in VIPSTYLE');
                    }
                    $hitType = match (true) {
                        ((int) $args[3]) !== 0 => HitType::BAZOOKA,
                        ((int) $args[8]) !== 0 => HitType::DOUBLE,
                        ((int) $args[9]) !== 0 => HitType::TENFOLD,
                        default                => HitType::NORMAL
                    };
                    $game->vipSettings = new VipSettings(
                        lives             : (int) $args[1],
                        ammo              : (int) $args[2],
                        respawn           : (((int) $args[12]) !== 0) ? (int) $args[13] : $game->respawn,
                        killTeam          : ((int) $args[5]) !== 0,
                        vipHitScore       : (int) $args[6],
                        hitType           : $hitType,
                        blastShots        : ((int) $args[14]) !== 0,
                        ignoreTeammateHits: ((int) $args[10]) !== 0
                    );
                    break;
                // VAMPIRESTYLE contains special mode settings (Zombies)
                // [0] On/Off - compatibility???
                // [1] Zombies are special players
                // [2] Zombie team
                // [3] Zombie lives
                // [4] Zombie ammo
                // [5] Infect after (value+1: 0 = 1 hit, 1 = 2 hits,...)
                // [6] Rainbow LED
                case 'VAMPIRESTYLE':
                    $game->zombieSettings = new ZombieSettings(
                        lives           : (int) $args[3],
                        ammo            : (int) $args[4],
                        infectHits      : ((int) $args[5]) + 1,
                        zombieSpecial   : ((int) $args[1]) !== 0,
                        zombieTeamNumber: (int) $args[2]
                    );
                    break;
                // SWITCHSTYLE contains special mode settings - Barvičky
                // [0] ON / OFF
                // [1] Number of hits before switch
                case 'SWITCHSTYLE':
                    // ASSISTEDSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] Blast shots
                    // [2] Double hits on/off
                    // [3] Ignore hits from teammates
                    // [4] Allow one trigger shooting
                    // [5] Change respawn
                    // [6] Respawn time
                    // [7] Ignore team hits scoring
                    // [8] Tenfold hits on/off
                    // [9] Bazooka on/off
                case 'ASSISTEDSTYLE':
                    // HITSTREAKSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] After how many hits
                    // [2] What powers are active: bin map (1 = 1st power, 2 = stealth power, 4 = blast power, 8 = 4th power)
                case 'HITSTREAKSTYLE':
                    // SHOWDOWNSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] Pack LEDs (0 = normal, 1 = dark, 2 = blink, 3 = power)
                    // [2] Blast shots
                    // [3] Duration (last minutes)
                    // [4] Shot type (0 = normal, 1 = double hits, 2 = bazooka)
                case 'SHOWDOWNSTYLE':
                    break;
                // ACTIVITYSTYLE contains special mode settings
                // [0] ON / OFF
                // [1] Bonus points for activity
                // [2] Active LEDs (0 = normal, 1 = off when stationary, 2 = off when active)
                case 'ACTIVITYSTYLE':
                    $game->scoring->activity = (int) ($args[1] ?? 0);
                    break;
                // KNOCKOUTSTYLE contains special mode settings
                // [0] ON / OFF
                // [1] Points for each higher knockout rank
                case 'KNOCKOUTSTYLE':
                    $game->scoring->knockout = (int) ($args[1] ?? 0);
                    break;
                // HITGAINSTYLE contains special mode settings
                // [0] Add ammo for each hit
                // [1] Add lives for each hit
                // [2] Add minutes for each hit (compatibility field)
                case 'HITGAINSTYLE':
                    $game->hitGainSettings = new HitGainSettings(
                        ammo : (int) ($args[0] ?? 0),
                        lives: (int) ($args[1] ?? 0)
                    );
                    break;
                // CROSSFIRESTYLE contains special mode settings
                // [0] ON / OFF
                case 'CROSSFIRESTYLE':
                    // PARALLELSTYLE contains special mode settings
                    // [0] ON / OFF
                case 'PARALLELSTYLE':
                    // SENSORTAGSTYLE contains special mode settings
                    // [0] ON / OFF
                case 'SENSORTAGSTYLE':
                    // ROCKPAPERSCISSORSSTYLE contains special mode settings
                    // [0] ON / OFF
                case 'ROCKPAPERSCISSORSSTYLE':
                break;
                // RESPAWNSTYLE contains special mode settings
                // [0] ON / OFF (0 = off, 1 = on, 2 = manual respawn)
                // [1] Respawn lives
                // [2] Respawn after (seconds: 30/60)
                case 'RESPAWNSTYLE':
                    $game->respawnSettings = new RespawnSettings(
                        respawnLives: (int) ($args[1] ?? 0)
                    );
                    break;
                // MINESTYLE contains pods settings
                // [0] Pod number
                // [1] ???
                // [2] Settings ID
                // [3] Team number (6 = all)
                // [4] Pod name
                case 'MINESTYLE':
                    break;
                // GROUP contains additional game notes
                // [0] Game title
                // [1] Game note (meta data)
                // [2] Solo team color
                case 'GROUP':
                    if ($argsCount !== 3) {
                        throw new ResultsParseException(
                            'Invalid argument count in GROUP - '.$argsCount.' '.json_encode(
                                $args,
                                JSON_THROW_ON_ERROR
                            )
                        );
                    }
                    // Parse metadata
                    $meta = $this->decodeMetadata($args[1]);
                    break;

                // PACK contains information about vest settings
                // [0] Vest number
                // [1] Player name
                // [2] Team number
                // [3] ???
                // [4] Special (VIP)
                // [5] One trigger shooting
                // [6] ???
                // [7] Birthday
                case 'PACK':
                    if ($argsCount !== 8) {
                        throw new ResultsParseException('Invalid argument count in PACK');
                    }
                    /** @var Evo6PlayerInterface $player */
                    $player = new ($game->playerClass);
                    $game->players->set($player, (int) $args[0]);
                    $player->setGame($game);
                    $player->vest = (int) $args[0];
                    $keysVests[$player->vest] = $currKey++;
                    $player->name = substr($args[1], 0, 15);
                    $player->teamNum = (int) $args[2];
                    $player->vip = ((int) $args[4]) !== 0;
                    $player->birthday = ((int) $args[7]) !== 0;
                    break;

                // TEAM contains team info
                // [0] Team number
                // [1] Team name
                // [2] Player count
                case 'TEAM':
                    if ($argsCount !== 3) {
                        throw new ResultsParseException('Invalid argument count in TEAM');
                    }
                    /** @var Evo6TeamInterface $team */
                    $team = new ($game->teamClass);
                    $game->teams->set($team, (int) $args[0]);
                    $team->setGame($game);
                    $team->name = substr($args[1], 0, 15);
                    $team->color = (int) $args[0];
                    $team->playerCount = (int) $args[2];

                    // Default team name
                    if ($team->name === '') {
                        $team->name = match ($team->color) {
                            0       => lang('Red team'),
                            1       => lang('Green team'),
                            2       => lang('Blue team'),
                            3       => lang('Pink team'),
                            4       => lang('Yellow team'),
                            5       => lang('Ocean team'),
                            default => lang('Team')
                        };
                    }
                    break;

                // PACKX contains player's results
                // [0] Vest number
                // [1] Score
                // [2] Shots
                // [3] Hits
                // [4] Deaths
                // [5] Position
                // [6] Lasermaxx results link
                // [7] Activity
                // [8] Calories
                case 'PACKX':
                    if ($argsCount !== 9) {
                        throw new ResultsParseException('Invalid argument count in PACKX');
                    }
                    /** @var Evo6PlayerInterface|null $player */
                    $player = $game->players->get((int) $args[0]);
                    if (!isset($player)) {
                        throw new ResultsParseException(
                            'Cannot find Player - '.json_encode(
                                $args[0],
                                JSON_THROW_ON_ERROR
                            ).PHP_EOL.$this->fileName.':'.PHP_EOL.$this->fileContents
                        );
                    }
                    $player->score = (int) $args[1];
                    $player->shots = (int) $args[2];
                    $player->hits = (int) $args[3];
                    $player->deaths = (int) $args[4];
                    $player->position = (int) $args[5];
                    $player->myLasermaxx = $args[6];
                    $player->activity = (int) $args[7];
                    $player->calories = (int) $args[8];
                    break;

                // PACKY contains player's additional results
                // [0] Vest number
                // [1] Score for shots
                // [2] Score for accuracy
                // [3] Score for powers
                // [4] Score for pod deaths
                // [5] Ammo remaining
                // [6] Accuracy
                // [7] Pod deaths
                // [8] ???
                // [9] ???
                // [10] ???
                // [11] ???
                // [12] Enemy hits
                // [13] Teammate hits
                // [14] Enemy deaths
                // [15] Teammate deaths
                // [16] Lives
                // [17] Time left (in seconds)
                // [18] Score for hits and deaths
                // [19] Score for VIP hits
                // [20] VIP Hits
                // [21] Score for activity
                // [22] Score for encouragement
                // [23] Time spend in game (in seconds)
                // [24] Score for knockout
                // [25] LMX Reality bonus
                // [26] bonus count
                // [27] penalty count
                // [28] penalty score
                case 'PACKY':
                    if ($argsCount !== 29) {
                        throw new ResultsParseException('Invalid argument count in PACKY');
                    }
                    /** @var Evo6PlayerInterface|null $player */
                    $player = $game->players->get((int) $args[0]);
                    if (!isset($player)) {
                        throw new ResultsParseException(
                            'Cannot find Player - '.json_encode(
                                $args[0],
                                JSON_THROW_ON_ERROR
                            ).PHP_EOL.$this->fileName.':'.PHP_EOL.$this->fileContents
                        );
                    }
                    $player->shotPoints = (int) ($args[1] ?? 0);
                    $player->scoreAccuracy = (int) ($args[2] ?? 0);
                    $player->scorePowers = (int) ($args[3] ?? 0);
                    $player->scoreMines = (int) ($args[4] ?? 0);

                    $player->ammoRest = max(0, (int) ($args[5] ?? 0));
                    $player->accuracy = (int) ($args[6] ?? 0);
                    $player->minesHits = (int) ($args[7] ?? 0);

                    $player->hitsOther = (int) ($args[12] ?? 0);
                    $player->hitsOwn = (int) ($args[13] ?? 0);
                    $player->deathsOther = (int) ($args[14] ?? 0);
                    $player->deathsOwn = (int) ($args[15] ?? 0);

                    $player->bonuses = (int) ($args[26] ?? 0);
                    $player->scoreVip = (int) ($args[19] ?? 0);
                    $player->scoreActivity = (int) ($args[21] ?? 0);
                    $player->scoreEncouragement = (int) ($args[22] ?? 0);
                    $player->scoreKnockout = (int) ($args[24] ?? 0);
                    $player->scoreReality = (int) ($args[25] ?? 0);
                    $player->scorePenalty = (int) ($args[28] ?? 0);
                    $player->penaltyCount = (int) ($args[27] ?? 0);
                    break;

                // PACKZ contains some player's additional results - probably player's deaths (duplicate from PACKY)
                // [0] Vest number
                // [1] ??? (Enemy deaths)
                // [2] ??? (Teammate deaths)
                case 'PACKZ':
                    break;

                // TEAMX contains information about team's score
                // [0] Team number
                // [1] Score
                // [2] Position
                // [3] Bonus score
                case 'TEAMX':
                    if ($argsCount !== 4) {
                        throw new ResultsParseException('Invalid argument count in TEAMX');
                    }
                    /** @var Evo6TeamInterface|null $team */
                    $team = $game->teams->get((int) $args[0]);
                    if (!isset($team)) {
                        throw new ResultsParseException(
                            'Cannot find Team - '.json_encode(
                                $args[0],
                                JSON_THROW_ON_ERROR
                            ).PHP_EOL.$this->fileName.':'.PHP_EOL.$this->fileContents
                        );
                    }
                    $team->score = (int) $args[1];
                    $team->position = (int) $args[2];
                    break;

                // HITS contain information about individual hits between players
                // [0] Vest number
                // [1...] X (X > 0) values for each player indicating how many times did a player with "Vest number" hit that player
                case 'HITS':
                    if ($argsCount < 2) {
                        throw new ResultsParseException('Invalid argument count in HITS');
                    }
                    /** @var Evo6PlayerInterface|null $player */
                    $player = $game->players->get((int) $args[0]);
                    if (!isset($player)) {
                        throw new ResultsParseException(
                            'Cannot find Player - '.json_encode(
                                $args[0],
                                JSON_THROW_ON_ERROR
                            ).PHP_EOL.$this->fileName.':'.PHP_EOL.$this->fileContents
                        );
                    }
                    foreach ($game->players as $player2) {
                        $player->addHits($player2, (int) ($args[$keysVests[$player2->vest] ?? -1] ?? 0));
                    }
                    break;

                // GAMECLONES contain information about cloned games
                case 'GAMECLONES':
                    // TODO: Detect clones and deal with them
                    break;
            }

            // TODO: Figure out the unknown arguments
        }
        // Set player teams
        foreach ($game->players->getAll() as $player) {
            // Find team
            foreach ($game->teams->getAll() as $team) {
                if ($player->teamNum === $team->color) {
                    $player->team = $team;
                    break;
                }
            }
        }

        // Process metadata
        if ($this->validateMetadata($meta, $game)) {
            $this->setMusicModeFromMeta($game, $meta);
            $this->setGroupFromMeta($game, $meta);
            $this->setPlayersMeta($game, $meta);
            $this->setTeamsMeta($game, $meta);

            $mode = $game->mode;
            if ($mode instanceof ModifyScoresMode) {
                $mode->modifyResults($game);
            }

            $this->processExtensions($game, $meta);
        }
        else {
            try {
                $logger = new Logger(LOG_DIR.'results/', 'import');
                $logger->warning('Game meta is not valid.', $meta);
            } catch (DirectoryCreationException) {
            }

            $mode = $game->mode;
            if ($mode instanceof ModifyScoresMode) {
                $mode->modifyResults($game);
            }

            $this->processExtensions($game, []);
        }

        return $game;
    }

    /**
     * Get arguments from a line
     *
     * Arguments are separated by a comma ',' character.
     *
     * @param  string  $args  Concatenated arguments
     *
     * @return string[] Separated and trimmed arguments, not type-casted
     */
    private function getArgs(string $args) : array {
        return array_map('trim', explode(',', $args));
    }
}

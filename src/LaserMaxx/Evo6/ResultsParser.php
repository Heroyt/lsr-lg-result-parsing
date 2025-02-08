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
 * @extends \App\Tools\AbstractResultsParser<Evo6GameInterface>
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
                    [$gameNumber, , $dateStart, $dateEnd, $playerCount] = $args;
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
                    // STYLEFLAGS
                    // [0] Force lasers off
                    // [1] Two trigger shooting
                    // [2] Pack sounds
                    // [3] Voice coach
                    // [4] Vibrations
                    // [5] Anti-stalking
                    // [6] Always blast shots
                    // [7] SWAT laser
                    // [8] SWAT light
                    // [9] Flash when hit
                    // [10] Double laser blast shots
                    // [11] ???
                    // [12] ???
                    // [13] Back sensor?
                    // [14] Front sensor
                    // [15] Gun sensor
                    // [16] Shoulder sensor?
                    // [17] ???
                    // [18] ???
                    // [19] ???
                    // [20] ???
                    // [21] Dimmed LEDs in game
                    // [22] Dimmed LEDs when armed
                    // [23] ???
                    // [24] ???
                    // [25] ???
                    // [26] ???
                    // [27] ???
                    // [28] ???
                    // [29] ???
                case 'STYLEFLAGS':
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
                // [6] ?Score per machine gun?
                // [7] ?Score per invisibility?
                // [8] ?Score per agent?
                // [9] ?Score per shield?
                // [10] ?Highscore?
                // [11] ???
                // [12] ???
                // [13] ???
                // [14] ???
                // [15] ???
                // [16] ???
                // [17] ???
                case 'SCORING':
                    if ($argsCount !== 18) {
                        throw new ResultsParseException('Invalid argument count in SCORING');
                    }
                    /** @var int[] $args */
                    $game->scoring = new Scoring(...$args);
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
                    // VIPSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 16 arguments
                case 'VIPSTYLE':
                    // VAMPIRESTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 6 unknown arguments (Lives, hits to infect, vampire team..?)
                case 'VAMPIRESTYLE':
                    // SWITCHSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] Number of hits before switch
                case 'SWITCHSTYLE':
                    // ASSISTEDSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 9 unknown arguments (respawn, allow one trigger shooting, ignore hits by teammates, machine gun,..)
                case 'ASSISTEDSTYLE':
                    // HITSTREAKSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 2 unknown arguments (number of hits, allowed bonuses)
                case 'HITSTREAKSTYLE':
                    // SHOWDOWNSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 4 unknown arguments (time before game, bazooka,...)
                case 'SHOWDOWNSTYLE':
                    // ACTIVITYSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] 2 unknown arguments
                case 'ACTIVITYSTYLE':
                    // KNOCKOUTSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] ???
                case 'KNOCKOUTSTYLE':
                    // HITGAINSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] ???
                    // [2] ???
                case 'HITGAINSTYLE':
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
                    // RESPAWNSTYLE contains special mode settings
                    // [0] ON / OFF
                    // [1] ??? (seconds to respawn)
                    // [2] ??? (invulnerability second)
                case 'RESPAWNSTYLE':
                    // MINESTYLE contains pods settings
                    // [0] Pod number
                    // [1] 1 unknown argument
                    // [2] Settings ID
                    // [3] Team number (6 = all)
                    // [4] Pod name
                case 'MINESTYLE':
                    break;
                // GROUP contains additional game notes
                // [0] Game title
                // [1] Game note (meta data)
                // [2] ???
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
                // [4] ?VIP?
                // [5] ???
                // [6] ???
                // [7] ???
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
                    $player->vip = $args[4] === '1';
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
                // [7] ???
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
                    $player->calories = (int) $args[8];
                    break;

                // PACKY contains player's additional results
                // - [0] Vest number
                // - [1] ?Score for shots
                // - [2] ?Score for bonuses
                // - [3] Score for powers
                // - [4] Score for pod deaths
                // - [5] Ammo remaining
                // - [6] Accuracy
                // - [7] Pod deaths
                // - [8] ???
                // - [9] ???
                // - [10] ???
                // - [11] ???
                // - [12] Enemy hits
                // - [13] Teammate hits
                // - [14] Enemy deaths
                // - [15] Teammate deaths
                // - [16] Lives
                // - [17] ???
                // - [18] Score for hits
                // - [19] ???
                // - [20] ???
                // - [21] ???
                // - [22] ???
                // - [23] ??? (930)
                // - [24] ???
                // - [25] ???
                // - [26] bonus count
                // - [27] ???
                // - [29] ???
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
                    $player->scoreBonus = (int) ($args[2] ?? 0);
                    $player->scorePowers = (int) ($args[3] ?? 0);
                    $player->scoreMines = (int) ($args[4] ?? 0);

                    $player->ammoRest = (int) ($args[5] ?? 0);
                    $player->accuracy = (int) ($args[6] ?? 0);
                    $player->minesHits = (int) ($args[7] ?? 0);

                    $player->hitsOther = (int) ($args[12] ?? 0);
                    $player->hitsOwn = (int) ($args[13] ?? 0);
                    $player->deathsOther = (int) ($args[14] ?? 0);
                    $player->deathsOwn = (int) ($args[15] ?? 0);

                    $player->bonuses = (int) ($args[26] ?? 0);
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
                // [3] ???
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

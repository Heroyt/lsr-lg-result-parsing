<?php

/**
 * @author Tomáš Vojík <xvojik00@stud.fit.vutbr.cz>, <vojik@wboy.cz>
 */

namespace Lsr\Lg\Results;

use Lsr\Exceptions\FileException;
use Lsr\LaserLiga\PlayerProviderInterface;
use Lsr\Lg\Results\Interface\GameModeProviderInterface;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\ResultsParserInterface;
use Lsr\Logging\Logger;

/**
 * Abstract base for any result parser class
 *
 * @template G of GameInterface
 * @implements ResultsParserInterface<G>
 */
abstract class AbstractResultsParser implements ResultsParserInterface
{
    /** @var array<string,string[][]> */
    protected array $matches = [];
    protected string $fileName = '';
    protected string $fileContents = '';

    /**
     * @param  PlayerProviderInterface  $playerProvider
     * @param  class-string<G>  $gameClass
     */
    public function __construct(
        protected readonly PlayerProviderInterface $playerProvider,
        protected readonly GameModeProviderInterface $gameModeProvider,
        protected readonly string $gameClass,
        protected readonly ?Logger $logger = null,
    ) {
        if (!class_exists($this->gameClass)) {
            throw new \InvalidArgumentException('Game class "'.$this->gameClass.'" does not exist');
        }
        if (!is_subclass_of($this->gameClass, GameInterface::class)) {
            throw new \InvalidArgumentException(
                'Game class "'.$this->gameClass.'" is not a valid game class (must implement GameInterface)'
            );
        }
    }

    /**
     * @return iterable<string>
     */
    public function getFileLines() : iterable {
        $separator = "\r\n";
        $line = strtok($this->getFileContents(), $separator);
        while ($line !== false) {
            yield $line;
            $line = strtok($separator);
        }
    }

    /**
     * @return string
     */
    public function getFileContents() : string {
        return $this->fileContents;
    }

    /**
     * @param  string  $pattern
     *
     * @return string[][]
     */
    public function matchAll(string $pattern) : array {
        if (isset($this->matches[$pattern])) {
            return $this->matches[$pattern];
        }
        preg_match_all($pattern, $this->getFileContents(), $matches);
        $this->matches[$pattern] = $matches;
        return $matches;
    }

    /**
     * @param  string  $fileName
     *
     * @return $this
     * @throws FileException
     */
    public function setFile(string $fileName) : static {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new FileException('File "'.$fileName.'" does not exist or is not readable');
        }

        $this->fileName = $fileName;

        $contents = file_get_contents($this->fileName);
        if ($contents === false) {
            throw new FileException('File "'.$this->fileName.'" read failed');
        }
        $this->fileContents = mb_convert_encoding($contents, 'UTF-8');
        $this->matches = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function setContents(string $contents) : static {
        $this->fileContents = $contents;
        $this->matches = [];
        return $this;
    }

    /**
     * @param  G  $game
     * @param  array<string, mixed>  $meta
     *
     * @return void
     */
    abstract protected function processExtensions(GameInterface $game, array $meta) : void;
}

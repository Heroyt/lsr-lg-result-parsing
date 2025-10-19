<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use InvalidArgumentException;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\ResultsGeneratorInterface;
use RuntimeException;

/**
 * Results file generator
 * @template Game of GameInterface
 * @template TGenerator of ResultsGeneratorInterface<Game>
 */
class ResultsGenerator
{
    /** @var TGenerator|null */
    protected ?ResultsGeneratorInterface $generator;
    /** @var Game|null */
    protected ?GameInterface $game = null;

    /**
     * @param array<non-empty-string, TGenerator> $systems List of available result generators for each system
     */
    public function __construct(
        protected readonly array $systems,
    )
    {
        foreach ($this->systems as $system => $parser) {
            /** @phpstan-ignore function.impossibleType */
            if (!is_subclass_of($parser, ResultsGeneratorInterface::class)) {
                throw new InvalidArgumentException('Invalid system settings for "' . $system . '"');
            }
        }
    }

    /**
     * Set the game to generate results for
     * @param Game $game
     * @return void
     */
    public function setGame(GameInterface $game): void
    {
        $this->game = $game;
        $this->generator = null;
    }

    /**
     * Generate results file content
     *
     * @return string
     */
    public function generate(): string
    {
        if ($this->game === null) {
            throw new RuntimeException('Game not set');
        }
        return $this->findGenerator()->generate($this->game);
    }

    /**
     * Find appropriate generator for the set game
     * @return TGenerator
     */
    protected function findGenerator(): ResultsGeneratorInterface
    {
        if (!isset($this->generator)) {
            if ($this->game === null) {
                throw new RuntimeException('Game not set');
            }

            foreach ($this->systems as $generator) {
                if ($generator::checkGame($this->game)) {
                    $this->generator = $generator;
                    return $this->generator;
                }
            }
        }
        if ($this->generator === null) {
            throw new RuntimeException('Cannot find generator for the given game');
        }
        return $this->generator;
    }

    /**
     * Generate results file and save it to given filename
     * @param non-empty-string $filename
     * @return bool
     */
    public function generateToFile(string $filename): bool
    {
        if ($this->game === null) {
            throw new RuntimeException('Game not set');
        }
        return $this->findGenerator()->generateToFile($this->game, $filename);
    }

}
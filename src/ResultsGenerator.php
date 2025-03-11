<?php
declare(strict_types=1);

namespace Lsr\Lg\Results;

use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\ResultsGeneratorInterface;

class ResultsGenerator
{
    protected ?ResultsGeneratorInterface $generator;
    protected ?GameInterface $game = null;

    /**
     * @param  array<non-empty-string, ResultsGeneratorInterface>  $systems
     */
    public function __construct(
        protected readonly array $systems,
    ) {
        foreach ($this->systems as $system => $parser) {
            if (!is_subclass_of($parser, ResultsGeneratorInterface::class)) {
                throw new \InvalidArgumentException('Invalid system settings for "'.$system.'"');
            }
        }
    }

    public function setGame(GameInterface $game) : void {
        $this->game = $game;
        $this->generator = null;
    }

    public function generate() : string {
        return $this->findGenerator()->generate($this->game);
    }

    protected function findGenerator() : ResultsGeneratorInterface {
        if (!isset($this->generator)) {
            if ($this->game === null) {
                throw new \RuntimeException('Game not set');
            }

            foreach ($this->systems as $generator) {
                if ($generator::checkGame($this->game)) {
                    $this->generator = $generator;
                    return $this->generator;
                }
            }
        }
        return $this->generator;
    }

    public function generateToFile(string $filename) : bool {
        return $this->findGenerator()->generateToFile($this->game, $filename);
    }

}
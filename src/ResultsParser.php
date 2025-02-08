<?php

namespace Lsr\Lg\Results;

use Lsr\Exceptions\FileException;
use Lsr\Lg\Results\Exception\ResultsParseException;
use Lsr\Lg\Results\Interface\Models\GameInterface;
use Lsr\Lg\Results\Interface\ResultsParserInterface;

class ResultsParser
{
    protected string $fileName = '';
    protected string $contents = '';
    private ?ResultsParserInterface $parser;

    /**
     * @param  array<non-empty-string, ResultsParserInterface>  $systems
     */
    public function __construct(
        protected readonly array $systems,
    ) {
        foreach ($this->systems as $system => $parser) {
            if (!is_subclass_of($parser, ResultsParserInterface::class)) {
                throw new \InvalidArgumentException('Invalid system settings for "'.$system.'"');
            }
        }
    }

    /**
     * Parse a given game file
     *
     * @return GameInterface
     * @throws ResultsParseException|FileException
     */
    public function parse() : GameInterface {
        return $this->findParser()->parse();
    }

    /**
     * @return ResultsParserInterface
     * @throws ResultsParseException|FileException
     */
    private function findParser() : ResultsParserInterface {
        if (!isset($this->parser)) {
            foreach ($this->systems as $parser) {
                if ($parser::checkFile($this->fileName, $this->contents)) {
                    $this->parser = $parser;
                    if (!empty($this->fileName)) {
                        $this->parser->setFile($this->fileName);
                    }
                    else {
                        $this->parser->setContents($this->contents);
                    }
                    return $this->parser;
                }
            }
            throw new ResultsParseException('Cannot find parser for given results file: '.$this->fileName);
        }
        return $this->parser;
    }

    /**
     * @throws FileException
     */
    public function setFile(string $fileName) : ResultsParser {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            throw new FileException('File "'.$fileName.'" does not exist or is not readable');
        }
        $this->fileName = $fileName;
        $this->contents = '';
        $this->parser = null;
        return $this;
    }

    public function setContents(string $contents) : ResultsParser {
        $this->fileName = '';
        $this->contents = $contents;
        $this->parser = null;
        return $this;
    }
}

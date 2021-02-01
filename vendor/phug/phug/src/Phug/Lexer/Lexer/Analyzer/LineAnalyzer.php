<?php

namespace Phug\Lexer\Analyzer;

use Phug\Lexer\Scanner\IndentationScanner;
use Phug\Lexer\Scanner\InterpolationScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\TokenInterface;
use Phug\Reader;

class LineAnalyzer
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var array
     */
    protected $lines;

    /**
     * @var int
     */
    protected $maxIndent = INF;

    /**
     * @var bool
     */
    protected $newLine = false;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var int
     */
    protected $newLevel;

    /**
     * @var bool
     */
    protected $allowedInterpolation = true;

    /**
     * @var bool
     */
    protected $outdent = false;

    public function __construct(State $state, Reader $reader, $lines = [])
    {
        $this->state = $state;
        $this->reader = $reader;
        $this->lines = $lines;
    }

    public function disallowInterpolation()
    {
        $this->allowedInterpolation = false;
    }

    public function analyze($quitOnOutdent, array $breakChars = [])
    {
        $this->outdent = false;
        $this->level = $this->state->getLevel();
        $this->newLevel = $this->level;
        $breakChars = array_merge($breakChars, [' ', "\t", "\n"]);
        $this->newLine = false;

        foreach ($this->hasChunksUntil($breakChars) as $lowerLevel) {
            if ($lowerLevel && $this->reader->match('[ \t]*\n')) {
                $this->reader->consume(mb_strlen($this->reader->getMatch(0)));
                $this->lines[] = [];

                continue;
            }

            $this->recordLine();

            if ($quitOnOutdent && !$this->newLine) {
                break;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasNewLine()
    {
        return (bool) $this->newLine;
    }

    /**
     * @return bool
     */
    public function hasOutdent()
    {
        return $this->outdent;
    }

    /**
     * @return array<array<string|TokenInterface>>
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * @return array<string>
     */
    public function getFlatLines()
    {
        return array_map(function ($line) {
            return implode('', $line);
        }, $this->lines);
    }

    /**
     * @return int
     */
    public function getMaxIndent()
    {
        return $this->maxIndent;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getNewLevel()
    {
        return $this->newLevel;
    }

    protected function getLineChunks()
    {
        $line = [];

        if ($this->allowedInterpolation) {
            foreach ($this->state->scan(InterpolationScanner::class) as $subToken) {
                $line[] = $subToken instanceof TextToken ? $subToken->getValue() : $subToken;
            }
        }

        if (($text = $this->reader->readUntilNewLine()) !== null) {
            $line[] = $text;
        }

        return $line;
    }

    protected function getLine()
    {
        $indent = $this->reader->match('[ \t]+(?=\S)') ? mb_strlen($this->reader->getMatch(0)) : INF;

        if ($indent < $this->maxIndent) {
            $this->maxIndent = $indent;
        }

        return $this->getLineChunks();
    }

    protected function recordLine()
    {
        $this->lines[] = $this->getLine();

        if ($this->newLine = $this->reader->peekNewLine()) {
            $this->reader->consume(1);
        }
    }

    protected function setNewLevel($newLevel, $first = false)
    {
        if (!$first || $newLevel > $this->newLevel) {
            $this->newLevel = $newLevel;
        }
    }

    protected function hasChunksUntil($breakChars)
    {
        $first = true;

        while ($this->reader->hasLength()) {
            $this->newLine = true;
            $indentationScanner = new IndentationScanner();
            $newLevel = $indentationScanner->getIndentLevel($this->state, $this->level);
            $this->setNewLevel($newLevel, $first);
            $first = false;

            if (!$this->reader->peekChars($breakChars)) {
                $this->outdent = $this->newLevel < $this->level;

                break;
            }

            yield $this->newLevel < $this->level;
        }
    }
}

<?php

namespace Phug\Lexer\Analyzer;

use Phug\Lexer\Scanner\IndentationScanner;
use Phug\Lexer\Scanner\InterpolationScanner;
use Phug\Lexer\State;
use Phug\Lexer\Token\TextToken;
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

    protected function getLine()
    {
        $line = [];
        $indent = $this->reader->match('[ \t]+(?=\S)') ? mb_strlen($this->reader->getMatch(0)) : INF;
        if ($indent < $this->maxIndent) {
            $this->maxIndent = $indent;
        }

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

    protected function recordLine()
    {
        $this->lines[] = $this->getLine();

        if ($this->newLine = $this->reader->peekNewLine()) {
            $this->reader->consume(1);
        }
    }

    public function analyze($quitOnOutdent, array $breakChars = [])
    {
        $this->level = $this->state->getLevel();
        $this->newLevel = $this->level;
        $breakChars = array_merge($breakChars, [' ', "\t", "\n"]);
        $this->newLine = false;

        while ($this->reader->hasLength()) {
            $this->newLine = true;
            $indentationScanner = new IndentationScanner();
            $this->newLevel = $indentationScanner->getIndentLevel($this->state, $this->level);

            if (!$this->reader->peekChars($breakChars)) {
                break;
            }

            if ($this->newLevel < $this->level) {
                if ($this->reader->match('[ \t]*\n')) {
                    $this->reader->consume(mb_strlen($this->reader->getMatch(0)));
                    $this->lines[] = [];

                    continue;
                }

                $this->state->setLevel($this->newLevel);

                break;
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
}

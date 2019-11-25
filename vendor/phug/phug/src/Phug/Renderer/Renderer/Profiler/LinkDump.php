<?php

namespace Phug\Renderer\Profiler;

use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent as CompilerNodeEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\Lexer\Token\MixinCallToken;
use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\Token\TextToken;
use Phug\Parser\Event\NodeEvent as ParserNodeEvent;
use Phug\Parser\Event\ParseEvent;
use Phug\Renderer\Event\HtmlEvent;

class LinkDump
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $style;

    private function initProperties($name, $events)
    {
        $style = [];
        foreach ([
            ['current', EndLexEvent::class, 'lexing', [
                'background' => '#7200c4',
                'color' => 'white',
            ]],
            ['current', HtmlEvent::class, 'rendering', [
                'background' => '#648481',
                'color' => 'white',
            ]],
            ['previous', CompileEvent::class, '%s', [
                'background' => '#ffff78',
            ]],
            ['current', ParseEvent::class, '%s', [
                'background' => '#a8ffff',
            ]],
            ['previous', FormatEvent::class, '%s rendering', [
                'background' => '#d8ffd8',
            ]],
            ['previous', CompilerNodeEvent::class, '%s compiling', [
                'background' => '#ffffa8',
            ]],
            ['previous', ParserNodeEvent::class, '%s parsing', [
                'background' => '#d8ffff',
            ]],
            ['previous', ElementEvent::class, '%s formatting', [
                'background' => '#d8d8ff',
            ]],
            ['previous', TokenEvent::class, '%s lexing', [
                'background' => '#ffd8d8',
            ]],
        ] as $condition) {
            list($conditionEvent, $conditionClass, $conditionName, $conditionStyle) = $condition;
            if (is_a($events[$conditionEvent], $conditionClass)) {
                $name = str_replace('%s', $name, $conditionName);
                $style = $conditionStyle;

                break;
            }
        }

        $this->name = $name;
        $this->style = $style;
    }

    public function __construct($link, $currentEvent, $previousEvent)
    {
        $name = $link instanceof TextToken
            ? 'text'
            : (method_exists($link, 'getName')
                ? $link->getName()
                : get_class($link)
            );
        if ($link instanceof MixinCallToken) {
            $name = '+'.$name;
        }
        if ($link instanceof MixinToken) {
            $name = 'mixin '.$name;
        }

        $this->initProperties($name, [
            'current'  => $currentEvent,
            'previous' => $previousEvent,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getStyle()
    {
        return $this->style;
    }
}

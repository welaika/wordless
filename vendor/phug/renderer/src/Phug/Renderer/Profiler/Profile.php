<?php

namespace Phug\Renderer\Profiler;

use Phug\Event;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Lexer\Event\TokenEvent;
use Phug\Renderer;
use SplObjectStorage;

class Profile
{
    /**
     * @var int
     */
    private $startTime = 0;

    /**
     * @var int
     */
    private $initialMemoryUsage = 0;

    /**
     * @var EventList
     */
    private $events = null;

    /**
     * @var SplObjectStorage
     */
    private $nodesRegister = null;

    /**
     * @var callable
     */
    private $eventDump = null;

    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(
        EventList $events,
        SplObjectStorage $nodesRegister,
        $startTime,
        $initialMemoryUsage,
        $eventDump
    ) {
        $this->events = $events;
        $this->nodesRegister = $nodesRegister;
        $this->startTime = $startTime;
        $this->initialMemoryUsage = $initialMemoryUsage;
        $this->eventDump = $eventDump;
    }

    private function getDuration($time, $precision = 3)
    {
        if (!$time) {
            return '0s';
        }
        $unit = 's';
        if ($precision >= 3) {
            $unit = 'ms';
            $time *= 1000;
            $precision -= 3;
        }
        if ($precision >= 3) {
            $unit = 'µs';
            $time *= 1000;
            $precision -= 3;
        }

        return round($time, $precision).$unit;
    }

    private function getProcesses($list, $link, $index, $duration, $maxSpace, $lineHeight, $timePrecision)
    {
        $count = count($list);
        for ($i = $count > 1 ? 1 : 0; $i < $count; $i++) {
            /** @var Event $previousEvent */
            $previousEvent = $list[max(0, $i - 1)];
            /** @var Event $currentEvent */
            $currentEvent = $list[$i];
            $min = $previousEvent->getParam('__time');
            $max = $currentEvent->getParam('__time');
            $end = $i === $count - 1 ? $maxSpace : $max;
            $linkDump = new LinkDump($link, $currentEvent, $previousEvent);
            $name = $linkDump->getName();
            $style = array_merge([
                'left'  => ($min * 100 / $duration).'%',
                'width' => (($end - $min) * 100 / $duration).'%',
                'top'   => (($index + 1) * $lineHeight).'px',
            ], $linkDump->getStyle());
            $time = $this->getDuration($max - $min, $timePrecision);
            yield (object) [
                'event'    => call_user_func($this->eventDump, $currentEvent),
                'previous' => $currentEvent === $previousEvent
                    ? '#current'
                    : call_user_func($this->eventDump, $previousEvent),
                'title'    => $name.': '.$time,
                'link'     => $name,
                'duration' => $time,
                'style'    => $style,
            ]; // @codeCoverageIgnore
        }
    }

    private function calculateIndex($lines, $min, $max)
    {
        $index = 0;
        foreach ($lines as $level => $line) {
            foreach ($line as $process) {
                list($from, $to) = $process;
                if ($to <= $min || $from >= $max) {
                    continue;
                }
                $index = $level + 1;
                continue 2;
            }
            break;
        }

        return $index;
    }

    public function compose($timePrecision, $lineHeight)
    {
        $duration = microtime(true) - $this->startTime;
        $linkedProcesses = new LinkedProcesses($this->events, $this->nodesRegister);

        $lines = [];
        $processes = [];
        foreach ($linkedProcesses as $link) {
            /** @var array $list */
            $list = $linkedProcesses[$link];
            $times = array_map(function (Event $event) {
                return $event->getParam('__time');
            }, $list);
            $min = min($times);
            $max = max(max($times), $min + $duration / 20);
            $index = $this->calculateIndex($lines, $min, $max);
            if (!isset($lines[$index])) {
                $lines[$index] = [];
            }
            $lines[$index][] = [$min, $max];
            $maxSpace = $max;
            if (count($list) === 1 && $list[0] instanceof TokenEvent) {
                $tokenDump = new TokenDump($list[0]->getToken());
                $tokenName = $tokenDump->getName();
                if ($tokenName) {
                    $processes[] = (object) [
                        'event'    => call_user_func($this->eventDump, $list[0]),
                        'previous' => '#current',
                        'title'    => $tokenName,
                        'link'     => $tokenDump->getSymbol(),
                        'duration' => '',
                        'style'    => [
                            'font-weight' => 'bold',
                            'font-size'   => '20px',
                            'background'  => '#d7d7d7',
                            'left'        => ($list[0]->getParam('__time') * 100 / $duration).'%',
                            'width'       => '3%',
                            'top'         => (($index + 1) * $lineHeight).'px',
                        ],
                    ]; // @codeCoverageIgnore

                    continue;
                }
            } // @codeCoverageIgnore
            $generator = $this->getProcesses($list, $link, $index, $duration, $maxSpace, $lineHeight, $timePrecision);
            foreach ($generator as $process) {
                $processes[] = $process;
            }
        }

        $this->parameters = [
            'processes' => $processes,
            'duration'  => $this->getDuration($duration, $timePrecision),
            'height'    => $lineHeight * (count($lines) + 1) + 81,
        ];
    }

    public function render()
    {
        return (new Renderer([
            'debug'           => false,
            'enable_profiler' => false,
            'default_format'  => HtmlFormat::class,
            'filters'         => [
                'no-php' => function ($text) {
                    return str_replace('<?', '<<?= "?" ?>', $text);
                },
            ],
        ]))->renderFile(__DIR__.'/resources/index.pug', $this->parameters);
    }
}

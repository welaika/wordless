<?php

namespace Phug\Renderer\Profiler;

use Phug\Event;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\DocumentNode;
use SplObjectStorage;

class LinkedProcesses extends SplObjectStorage
{
    /**
     * @var SplObjectStorage
     */
    private $nodesRegister = null;

    public function __construct(EventList $events, SplObjectStorage $nodesRegister)
    {
        $this->nodesRegister = $nodesRegister;

        if ($events) {
            foreach ($events as $event) {
                /* @var Event $event */
                $link = $this->getEventLink($event);
                if (!($link instanceof TokenInterface) && !method_exists($link, 'getName')) {
                    $link = $link instanceof DocumentNode
                        ? $this->getProfilerEvent('document', $link)
                        : $event;
                }
                if (!isset($this[$link])) {
                    $this[$link] = [];
                }
                $list = $this[$link];
                $list[] = $event;
                $this[$link] = $list;
            }
        }
    }

    private function getEventLink(Event $event)
    {
        return isset($event->getParams()['__link'])
            ? $event->getParam('__link')
            : null;
    }

    private function getProfilerEvent($name, $object)
    {
        if (!isset($this->nodesRegister[$object])) {
            $this->nodesRegister[$object] = new Event($name);
        }

        return $this->nodesRegister[$object];
    }
}

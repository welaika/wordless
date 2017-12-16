<?php

namespace Phug\Renderer\Profiler;

use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\CompilerEvent;
use Phug\Event;
use Phug\Formatter;
use Phug\Formatter\Event\DependencyStorageEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\FormatterEvent;
use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\LexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\LexerEvent;
use Phug\Parser\Event\NodeEvent as ParserNodeEvent;
use Phug\Parser\Event\ParseEvent;
use Phug\Parser\NodeInterface;
use Phug\ParserEvent;
use Phug\Renderer\Event\HtmlEvent;
use Phug\Renderer\Event\RenderEvent;
use Phug\RendererEvent;
use Phug\Util\AbstractModule;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\SandBox;
use SplObjectStorage;

class ProfilerModule extends AbstractModule
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
    private static $profilers = [];

    /**
     * @var int
     */
    private static $profilersIndex = 0;

    public function __construct(EventList $events, ModuleContainerInterface $container)
    {
        parent::__construct($container);

        $this->events = $events;
        $this->initialize();
    }

    public function initialize()
    {
        $this->startTime = microtime(true);
        $this->initialMemoryUsage = memory_get_usage();
        $this->nodesRegister = new SplObjectStorage();

        if (!$this->getContainer()->hasOption('profiler.dump_event')) {
            $this->getContainer()->setOption('profiler.dump_event', [$this, 'getEventDump']);
        }

        $this->eventDump = $this->getContainer()->getOption('profiler.dump_event');

        if (in_array($this->eventDump, ['var_dump', 'print_r'])) {
            $function = $this->eventDump;
            $this->eventDump = function ($value) use ($function) {
                return $this->getFunctionDump($value, $function);
            };
        }
    }

    public function reset()
    {
        $this->initialize();
        while (count($this->events)) {
            $this->events->offsetUnset(key($this->events));
        }
        $this->events->unlock();
    }

    public function kill()
    {
        $this->events->lock();
    }

    public function isAlive()
    {
        return !$this->events->isLocked();
    }

    private function appendParam(Event $event, $key, $value)
    {
        $event->setParams(array_merge($event->getParams(), [
            $key => $value,
        ]));
    }

    private function appendNode(Event $event, $node)
    {
        if ($node instanceof NodeInterface) {
            $this->appendParam($event, '__location', $node->getSourceLocation());
            $this->appendParam($event, '__link', $node->getToken() ?: $node);
        }
    }

    private function throwException(Event $event, $message)
    {
        $params = $event->getParams();
        $this->kill();

        throw isset($params['__location'])
            ? new ProfilerLocatedException($params['__location'], $message)
            : new ProfilerException($message);
    }

    private function record(Event $event)
    {
        $time = microtime(true) - $this->startTime;
        $container = $this->getContainer();
        $maxTime = $container->getOption('execution_max_time');
        if ($maxTime > -1 && $time * 1000 > $maxTime) {
            $this->throwException($event, 'execution_max_time of '.$maxTime.'ms exceeded.');
        } // @codeCoverageIgnore
        $memoryLimit = $container->getOption('memory_limit');
        if ($memoryLimit > -1 && memory_get_usage() - $this->initialMemoryUsage > $memoryLimit) {
            $this->throwException($event, 'memory_limit of '.$memoryLimit.'B exceeded.');
        } // @codeCoverageIgnore
        $this->appendParam($event, '__time', $time);
        if ($container->getOption('profiler.display') || $container->getOption('profiler.log')) {
            $this->events[] = $event;
        }
    }

    /**
     * @return EventList
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Catch output of a dump function and return it as string.
     *
     * @param mixed    $value
     * @param callable $function
     *
     * @return string
     */
    public function getFunctionDump($value, $function = 'var_dump')
    {
        $sandBox = new SandBox(function () use ($function, $value) {
            $function($value);
        });

        return $sandBox->getBuffer();
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    public function getEventDump(Event $event)
    {
        $dump = new Dump($event);

        return $dump->dump();
    }

    private function renderProfile()
    {
        $display = $this->getContainer()->getOption('profiler.display');
        $log = $this->getContainer()->getOption('profiler.log');
        if (!$display && !$log) {
            return '';
        }

        $profile = new Profile(
            $this->events,
            $this->nodesRegister,
            $this->startTime,
            $this->initialMemoryUsage,
            $this->eventDump
        );

        $profile->compose(
            $this->getContainer()->getOption('profiler.time_precision'),
            $this->getContainer()->getOption('profiler.line_height')
        );

        $this->kill();

        $render = $profile->render();

        $this->cleanupProfilerNodes();

        if ($log) {
            file_put_contents($log, $render);
        }

        return $display ? $render : '';
    }

    public function getDebugId($nodeId)
    {
        $index = static::$profilersIndex++;
        static::$profilers[$index] = [$this, $nodeId];

        return $index;
    }

    public function recordDisplayEvent($nodeId)
    {
        if (!$this->isAlive()) {
            return;
        }
        /** @var Formatter $formatter */
        $formatter = $this->getContainer();
        if ($formatter->debugIdExists($nodeId)) {
            $event = new Event('display');
            $this->appendNode($event, $formatter->getNodeFromDebugId($nodeId));
            $this->record($event);
        }
    }

    public static function recordProfilerDisplayEvent($debugId)
    {
        if (isset(static::$profilers[$debugId])) {
            /** @var ProfilerModule $profiler */
            list($profiler, $nodeId) = static::$profilers[$debugId];
            $profiler->recordDisplayEvent($nodeId);
        }
    }

    private function cleanupProfilerNodes()
    {
        static::$profilers = array_filter(static::$profilers, function ($params) {
            return $params[0] !== $this;
        });
    }

    public function attachEvents()
    {
        parent::attachEvents();
        $formatter = $this->getContainer();
        if ($formatter instanceof Formatter) {
            $formatter->setOption('patterns.debug_comment', function ($nodeId) use ($formatter) {
                return "\n".($nodeId !== ''
                        ? '\\'.static::class.'::recordProfilerDisplayEvent('.
                            var_export($this->getDebugId($nodeId), true).
                        ");\n"
                        : ''
                    )."// PUG_DEBUG:$nodeId\n";
            });
        }
    }

    private function getCompilerEventListeners()
    {
        return [
            CompilerEvent::COMPILE => function (CompileEvent $event) {
                $this->appendParam($event, '__link', $event);
            },
            CompilerEvent::ELEMENT => function (ElementEvent $event) {
                $this->appendNode($event, $event->getElement()->getOriginNode());
            },
            CompilerEvent::NODE => function (NodeEvent $event) {
                $this->appendNode($event, $event->getNode());
            },
            CompilerEvent::OUTPUT => function (OutputEvent $event) {
                $this->appendParam($event, '__link', $event->getCompileEvent());
            },
        ];
    }

    private function getFormatterEventListeners()
    {
        return [
            FormatterEvent::DEPENDENCY_STORAGE => function (DependencyStorageEvent $event) {
                $this->appendParam($event, '__link', $event->getDependencyStorage());
            },
            FormatterEvent::FORMAT => function (FormatEvent $event) {
                $this->appendNode($event, $event->getElement()->getOriginNode());
            },
        ];
    }

    private function getParserEventListeners()
    {
        return [
            ParserEvent::PARSE => function (ParseEvent $event) {
                $this->appendParam($event, '__link', $event);
            },
            ParserEvent::DOCUMENT => function (ParserNodeEvent $event) {
                $this->appendNode($event, $event->getNode());
            },
            ParserEvent::STATE_ENTER => function (ParserNodeEvent $event) {
                $this->appendNode($event, $event->getNode());
            },
            ParserEvent::STATE_LEAVE => function (ParserNodeEvent $event) {
                $this->appendNode($event, $event->getNode());
            },
            ParserEvent::STATE_STORE => function (ParserNodeEvent $event) {
                $this->appendNode($event, $event->getNode());
            },
        ];
    }

    private function getLexerEventListeners()
    {
        return [
            LexerEvent::LEX => function (LexEvent $event) {
                $this->appendParam($event, '__link', $event);
            },
            LexerEvent::END_LEX => function (EndLexEvent $event) {
                $this->appendParam($event, '__link', $event->getLexEvent());
            },
            LexerEvent::TOKEN => function (TokenEvent $event) {
                $token = $event->getToken();
                $this->appendParam($event, '__location', $token->getSourceLocation());
                $this->appendParam($event, '__link', $token);
            },
        ];
    }

    public function getEventListeners()
    {
        $eventListeners = array_map(function (callable $eventListener) {
            return function (Event $event) use ($eventListener) {
                if ($this->isAlive() && $eventListener($event) !== false) {
                    $this->record($event);
                }
            };
        }, array_merge(
            [
                RendererEvent::RENDER => function (RenderEvent $event) {
                    $this->appendParam($event, '__link', $event);
                },
            ],
            $this->getCompilerEventListeners(),
            $this->getFormatterEventListeners(),
            $this->getParserEventListeners(),
            $this->getLexerEventListeners()
        ));

        $eventListeners[RendererEvent::HTML] = function (HtmlEvent $event) {
            $this->appendParam($event, '__link', $event->getRenderEvent());
            if ($this->isAlive()) {
                $this->record($event);
            }

            if ($event->getBuffer()) {
                $event->setBuffer($this->renderProfile().$event->getBuffer());
            }

            if (is_string($event->getResult())) {
                $event->setResult($this->renderProfile().$event->getResult());
            }
        };

        return $eventListeners;
    }
}

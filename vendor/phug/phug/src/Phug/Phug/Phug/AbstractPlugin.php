<?php

namespace Phug;

use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\Event\DependencyStorageEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\Formatter\Event\StringifyEvent;
use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\LexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Event\ParseEvent;
use Phug\Parser\NodeInterface;
use Phug\Partial\CallbacksTrait;
use Phug\Partial\PluginEnablerTrait;
use Phug\Partial\PluginEventsTrait;
use Phug\Partial\TokenGeneratorTrait;
use Phug\Renderer\Event\HtmlEvent;
use Phug\Renderer\Event\RenderEvent;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\OptionTrait;
use ReflectionClass;
use ReflectionException;

/**
 * A plug-in can be used both as an extension (globally enabled with MyPlugin::enable()) or
 * as a module scoped to a given renderer (with MyPlugin::enable($renderer)).
 */
abstract class AbstractPlugin extends AbstractExtension implements RendererModuleInterface
{
    use CallbacksTrait;
    use OptionTrait;
    use PluginEnablerTrait;
    use PluginEventsTrait;
    use TokenGeneratorTrait;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var null|array
     */
    private $eventToContainerMap = null;

    protected $methodTypes = [
        'handleTokenEvent'  => [TokenInterface::class, LexerEvent::TOKEN],
        'handleNodeEvent'   => [NodeInterface::class, CompilerEvent::NODE],
        'handleFormatEvent' => [ElementInterface::class, FormatterEvent::FORMAT],
    ];

    protected $types = [
        ParseEvent::class             => ParserEvent::PARSE,
        NodeEvent::class              => CompilerEvent::NODE,
        CompileEvent::class           => CompilerEvent::COMPILE,
        OutputEvent::class            => CompilerEvent::OUTPUT,
        ElementEvent::class           => CompilerEvent::ELEMENT,
        TokenEvent::class             => LexerEvent::TOKEN,
        LexEvent::class               => LexerEvent::LEX,
        EndLexEvent::class            => LexerEvent::END_LEX,
        FormatEvent::class            => FormatterEvent::FORMAT,
        StringifyEvent::class         => FormatterEvent::STRINGIFY,
        DependencyStorageEvent::class => FormatterEvent::DEPENDENCY_STORAGE,
        RenderEvent::class            => RendererEvent::RENDER,
        HtmlEvent::class              => RendererEvent::HTML,
    ];

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer->setOptions(Phug::getExtensionsOptions([static::class]));
    }

    public function getContainer()
    {
        return $this->renderer;
    }

    /**
     * @param TokenEvent $tokenEvent
     *
     * @throws ReflectionException
     */
    public function handleTokenEvent(TokenEvent $tokenEvent)
    {
        $tokenEvent->setTokenGenerator(
            $this->getTokenGenerator(
                $this->getCallbacks(__METHOD__),
                $tokenEvent->getTokenGenerator() ?: static::toArrayIfTruthy($tokenEvent->getToken())
            )
        );
    }

    /**
     * @param NodeEvent $nodeEvent
     *
     * @throws ReflectionException
     */
    public function handleNodeEvent(NodeEvent $nodeEvent)
    {
        $node = $nodeEvent->getNode();

        foreach ($this->getCallbacks(__METHOD__) as $callback) {
            if (is_a($node, Invoker::getCallbackType($callback))) {
                $node = $callback($node) ?: $node;
            }
        }

        $nodeEvent->setNode($node);
    }

    /**
     * @param FormatEvent $formatEvent
     *
     * @throws ReflectionException
     */
    public function handleFormatEvent(FormatEvent $formatEvent)
    {
        $element = $formatEvent->getElement();

        foreach ($this->getCallbacks(__METHOD__) as $callback) {
            if (is_a($element, Invoker::getCallbackType($callback))) {
                $element = $callback($element, $formatEvent->getFormat()) ?: $element;
            }
        }

        $formatEvent->setElement($element);
    }

    /**
     * Get events lists to be sorted.
     *
     * @throws ReflectionException
     *
     * @return array[]
     */
    public function getEventsList()
    {
        $listeners = [];
        $methods = [];

        foreach ($this->getMethodsByPrefix('on') as $method) {
            $callback = [$this, $method];
            $type = Invoker::getCallbackType($callback);

            if ($this->addSpecificCallback($methods, $type, $callback)) {
                continue;
            }

            $type = isset($this->types[$type]) ? $this->types[$type] : $type;
            $listeners[] = [$type, $callback];
        }

        foreach ($methods as $method => $eventName) {
            $listeners[] = [$eventName, [$this, $method]];
        }

        return $listeners;
    }

    /**
     * Get the current renderer instance (container of the plugin).
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Get the current compiler instance (used by the renderer).
     *
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->renderer->getCompiler();
    }

    /**
     * Get the current formatter instance (used by the compiler).
     *
     * @return Formatter
     */
    public function getFormatter()
    {
        return $this->getCompiler()->getFormatter();
    }

    /**
     * Get the current parser instance (used by the compiler).
     *
     * @return Parser
     */
    public function getParser()
    {
        return $this->getCompiler()->getParser();
    }

    /**
     * Get the current lexer instance (used by the parser).
     *
     * @return Lexer
     */
    public function getLexer()
    {
        return $this->getParser()->getLexer();
    }

    /**
     * Get the container able to listen the given event.
     *
     * @param string $event the event to be listenable
     *
     * @throws ReflectionException
     *
     * @return ModuleContainerInterface
     */
    public function getEventContainer($event)
    {
        $map = $this->getEventToContainerMap();
        $class = isset($map[$event]) ? $map[$event] : 'Renderer';

        return $this->{'get'.$class}();
    }

    /**
     * Attaches a listener to an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     * @param int      $priority the priority at which the $callback executed
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    public function attachEvent($event, $callback, $priority = 0)
    {
        return $this->getEventContainer($event)->attach($event, $callback, $priority);
    }

    /**
     * Detaches a listener from an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    public function detachEvent($event, $callback)
    {
        return $this->getEventContainer($event)->detach($event, $callback);
    }

    /**
     * @throws ReflectionException
     *
     * @return iterable|string[]
     */
    protected function getClassForEvents()
    {
        foreach (['Compiler', 'Formatter', 'Parser', 'Lexer'] as $class) {
            foreach ((new ReflectionClass('Phug\\'.$class.'Event'))->getConstants() as $constant) {
                yield $constant => $class;
            }
        }
    }

    /**
     * @throws ReflectionException
     *
     * @return array
     */
    protected function getEventToContainerMap()
    {
        if ($this->eventToContainerMap === null) {
            $this->eventToContainerMap = [];

            foreach ($this->getClassForEvents() as $constant => $class) {
                $this->eventToContainerMap[$constant] = $class;
            }
        }

        return $this->eventToContainerMap;
    }

    protected function getMethodsByPrefix($prefix)
    {
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/^'.$prefix.'[A-Z]/', $method)) {
                yield $method;
            }
        }
    }

    protected function addSpecificCallback(&$methods, $type, $callback)
    {
        foreach ($this->methodTypes as $methodName => list($className, $eventName)) {
            if (is_a($type, $className, true)) {
                $methods[$methodName] = $eventName;
                $this->addCallback($methodName, $callback);

                return true;
            }
        }

        return false;
    }

    private static function toArrayIfTruthy($value)
    {
        return $value ? [$value] : [];
    }
}

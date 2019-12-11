<?php

namespace Phug;

// Elements
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\ElementInterface;
// Formats
use Phug\Formatter\Event\DependencyStorageEvent;
use Phug\Formatter\Event\FormatEvent;
use Phug\Formatter\Event\NewFormatEvent;
use Phug\Formatter\Event\StringifyEvent;
use Phug\Formatter\Format\BasicFormat;
use Phug\Formatter\Format\FramesetFormat;
use Phug\Formatter\Format\HtmlFormat;
use Phug\Formatter\Format\MobileFormat;
use Phug\Formatter\Format\OneDotOneFormat;
use Phug\Formatter\Format\PlistFormat;
use Phug\Formatter\Format\StrictFormat;
use Phug\Formatter\Format\TransitionalFormat;
use Phug\Formatter\Format\XmlFormat;
use Phug\Formatter\FormatInterface;
// Utils
use Phug\Parser\NodeInterface;
use Phug\Util\Exception\LocatedException;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\LevelTrait;
use Phug\Util\Partial\ModuleContainerTrait;
use Phug\Util\SourceLocation;
use SplObjectStorage;

class Formatter implements ModuleContainerInterface
{
    use LevelTrait;
    use ModuleContainerTrait;

    /**
     * @var FormatInterface|string
     */
    private $format;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var DependencyInjection
     */
    private $dependencies;

    /**
     * @var DependencyInjection
     */
    private $mixins;

    /**
     * @var array
     */
    private $mixinsPreCalled = [];

    /**
     * @var SplObjectStorage
     */
    private $destructors;

    /**
     * @var bool
     */
    private $mixinsAllRequired = false;

    /**
     * @var array
     */
    private $debugNodes = [];

    /**
     * Creates a new formatter instance.
     *
     * The formatter will turn DocumentNode tree into a PHTML string
     *
     * @param array|null $options the options array
     */
    public function __construct($options = null)
    {
        $this->initFormats()->setOptionsDefaults($options ?: [], [
            'debug'                        => false,
            'located_exception_class_name' => LocatedException::class,
            'dependencies_storage'         => 'pugModule',
            'default_format'               => BasicFormat::class,
            'doctype'                      => null,
            'pug_variables_variable_name'  => null,
            'formats'                      => [
                'basic'        => BasicFormat::class,
                'frameset'     => FramesetFormat::class,
                'html'         => HtmlFormat::class,
                'mobile'       => MobileFormat::class,
                '1.1'          => OneDotOneFormat::class,
                'plist'        => PlistFormat::class,
                'strict'       => StrictFormat::class,
                'transitional' => TransitionalFormat::class,
                'xml'          => XmlFormat::class,
            ],
            'formatter_modules'            => [],

            'on_format'             => null,
            'on_stringify'          => null,
            'on_new_format'         => null,
            'on_dependency_storage' => null,
        ]);

        $formatClassName = $this->getOption('default_format');

        if (!is_a($formatClassName, FormatInterface::class, true)) {
            throw new \RuntimeException(
                "Passed default format class $formatClassName must ".
                'implement '.FormatInterface::class
            );
        }

        // Throw exception if a format is wrong.
        foreach ($this->getOption('formats') as $doctype => $format) {
            $this->setFormatHandler($doctype, $format);
        }

        $this->format = $formatClassName;

        if ($onFormat = $this->getOption('on_format')) {
            $this->attach(FormatterEvent::FORMAT, $onFormat);
        }

        if ($onStringify = $this->getOption('on_stringify')) {
            $this->attach(FormatterEvent::STRINGIFY, $onStringify);
        }

        if ($onNewFormat = $this->getOption('on_new_format')) {
            $this->attach(FormatterEvent::NEW_FORMAT, $onNewFormat);
        }

        if ($onDependencyStorage = $this->getOption('on_dependency_storage')) {
            $this->attach(FormatterEvent::DEPENDENCY_STORAGE, $onDependencyStorage);
        }

        $this->addModules($this->getOption('formatter_modules'));

        $doctype = $this->getOption('doctype');

        if ($doctype) {
            $this->setFormat($doctype);
        }
    }

    /**
     * Store a node in a debug list and return the allocated index for it.
     *
     * @param NodeInterface $node
     *
     * @return int
     */
    public function storeDebugNode(NodeInterface $node)
    {
        $nodeId = count($this->debugNodes);
        $this->debugNodes[] = $node;

        return $nodeId;
    }

    private function fileContains($file, $needle)
    {
        $handler = @fopen($file, 'r');
        if (!$handler) {
            return false;
        }
        $previousChunk = '';
        while ($chunk = fread($handler, 512)) {
            if (mb_strrpos($previousChunk.$chunk, $needle) !== false) {
                fclose($handler);

                return true;
            }
            $previousChunk = $chunk;
        }
        fclose($handler);

        return false;
    }

    private function getSourceLine($error)
    {
        $previous = null;
        $line = null;

        /** @var \Throwable $error */
        foreach (array_merge([[
            'file' => $error->getFile(),
            'line' => $error->getLine(),
        ]], $error->getTrace()) as $step) {
            if (isset($step['function']) && $step['function'] === 'eval') {
                $line = $previous;
                continue;
            }
            $previous = isset($step['line']) ? $step['line'] : 1;
            if (!is_null($line)) {
                if (isset($step['args'], $step['args'][0]) &&
                    mb_strrpos($step['args'][0], 'PUG_DEBUG:') !== false
                ) {
                    return $line;
                }
                $line = null;
            }
            foreach (['php', '__pug_php'] as $key) {
                if (isset($step['args'], $step['args'][4]) &&
                    is_array($step['args'][4]) &&
                    isset($step['args'][4][$key]) &&
                    mb_strrpos($step['args'][4][$key], 'PUG_DEBUG:') !== false
                ) {
                    if (isset($step['line'])) {
                        return $step['line'];
                    }
                    if (isset($step['args'][3])) {
                        return $step['args'][3];
                    }
                }
            }
            if (isset($step['file'], $step['line']) && (
                strpos($step['file'], "eval()'d code") !== false ||
                $this->fileContains($step['file'], 'PUG_DEBUG:')
            )) {
                return $step['line'];
            }
        }

        return false;
    }

    /**
     * @param int $nodeId
     *
     * @return bool
     */
    public function debugIdExists($nodeId)
    {
        return isset($this->debugNodes[$nodeId]);
    }

    /**
     * @param int $nodeId
     *
     * @return NodeInterface
     */
    public function getNodeFromDebugId($nodeId)
    {
        return $this->debugNodes[$nodeId];
    }

    /**
     * Return a formatted error linked to pug source.
     *
     * @param \Throwable $error
     * @param string     $code
     * @param string     $path
     *
     * @throws \Throwable
     *
     * @return LocatedException|\Throwable
     */
    public function getDebugError($error, $code, $path = null)
    {
        /** @var \Throwable $error */
        $line = $this->getSourceLine($error);
        if ($line === false) {
            return $error;
        }
        $source = explode("\n", $code, max(2, $line));
        array_pop($source);
        $source = implode("\n", $source);
        $pos = mb_strrpos($source, 'PUG_DEBUG:');
        if ($pos === false) {
            throw $error;
        }
        $nodeId = intval(mb_substr($source, $pos + 10, 32));
        if (!$this->debugIdExists($nodeId)) {
            throw $error;
        }
        $node = $this->getNodeFromDebugId($nodeId);
        $nodeLocation = $node->getSourceLocation();
        $location = new SourceLocation(
            ($nodeLocation ? $nodeLocation->getPath() : null) ?: $path,
            $nodeLocation ? $nodeLocation->getLine() : 0,
            $nodeLocation ? $nodeLocation->getOffset() : 0,
            $nodeLocation ? $nodeLocation->getOffsetLength() : 0
        );
        $className = $this->getOption('located_exception_class_name');

        return new $className(
            $location,
            $error->getMessage(),
            $error->getCode(),
            $error
        );
    }

    /**
     * Set the format handler for a given doctype identifier.
     *
     * @param string                 $doctype doctype identifier
     * @param FormatInterface|string $format  format handler
     *
     * @return $this
     */
    public function setFormatHandler($doctype, $format)
    {
        if (!is_a($format, FormatInterface::class, true)) {
            throw new \InvalidArgumentException(
                "Passed format class $format must ".
                'implement '.FormatInterface::class
            );
        }
        $this->setOption(['formats', $doctype], $format);

        return $this;
    }

    /**
     * Return current format.
     *
     * @return FormatInterface|string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Initialize the formats list and dependencies.
     *
     * @return $this
     */
    public function initFormats()
    {
        $this->dependencies = new DependencyInjection();
        $this->mixins = new DependencyInjection();
        $this->destructors = new SplObjectStorage();
        $this->formats = [];

        return $this;
    }

    /**
     * Return current format as instance of FormatInterface.
     *
     * @param FormatInterface|string optional format, if missing current format is used
     *
     * @return FormatInterface
     */
    public function getFormatInstance($format = null)
    {
        $format = $format ?: $this->format;

        if (!($format instanceof FormatInterface)) {
            if (!isset($this->formats[$format])) {
                $event = new NewFormatEvent($this, new $format($this));
                $this->trigger($event);
                $this->formats[$format] = $event->getFormat();
            }

            $format = $this->formats[$format];
        }

        return $format;
    }

    /**
     * Handle PHP code with the pattern php_handle_code.
     *
     * @param string $phpCode
     *
     * @return string
     */
    public function handleCode($phpCode)
    {
        return $this->getFormatInstance()->handleCode($phpCode);
    }

    /**
     * Format a code with transform_expression and tokens handlers.
     *
     * @param string $code             input code
     * @param bool   $checked          rather the expression is checked for escaping or not
     * @param bool   $noTransformation disable transform_expression
     *
     * @return string
     */
    public function formatCode($code, $checked = false, $noTransformation = false)
    {
        return $this->getFormatInstance()->formatCode($code, $checked, $noTransformation);
    }

    /**
     * Return an expression to be casted as boolean according to expression_in_bool pattern.
     *
     * @param string $code             input code
     * @param bool   $checked          rather the expression is checked for escaping or not
     * @param bool   $noTransformation disable transform_expression
     *
     * @return string
     */
    public function formatBoolean($code, $checked = false, $noTransformation = false)
    {
        return $this->getFormatInstance()->formatBoolean($this->formatCode($code, $checked, $noTransformation));
    }

    /**
     * @param array $attributes
     *
     * @return ExpressionElement
     */
    public function formatAttributesList($attributes)
    {
        return $this->getFormatInstance()->formatAttributesList($attributes);
    }

    /**
     * Set a format name as the current or fallback to default if not available.
     *
     * @param string $doctype format identifier
     *
     * @return $this
     */
    public function setFormat($doctype)
    {
        $formats = $this->getOption('formats');
        $this->format = empty($formats[$doctype])
            ? $this->getOption('default_format')
            : $formats[$doctype];

        return $this;
    }

    /**
     * @return SplObjectStorage
     */
    public function getDestructors()
    {
        return $this->destructors;
    }

    /**
     * @return DependencyInjection
     */
    public function getMixins()
    {
        return $this->mixins;
    }

    /**
     * @return $this
     */
    public function requireAllMixins()
    {
        $this->mixinsAllRequired = true;

        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function requireMixin($name)
    {
        $this->mixins->has($name)
            ? $this->mixins->setAsRequired($name)
            : array_push($this->mixinsPreCalled, $name);

        return $this;
    }

    /**
     * Create/reset the dependency injector.
     */
    public function formatDependencies()
    {
        $variablesVariable = $this->getOption('pug_variables_variable_name');

        $dependencies = $variablesVariable ? implode("\n", [
            '<?php',
            '$'.$variablesVariable.' = [];',
            'foreach (array_keys(get_defined_vars()) as $__pug_key) {',
            '    $'.$variablesVariable.'[$__pug_key] = &$$__pug_key;',
            '}',
            '?>',
        ]) : '';

        if ($this->dependencies->countRequiredDependencies() > 0) {
            $dependenciesExport = $this->dependencies->export(
                $this->getOption('dependencies_storage')
            );

            $dependencies .= $this->format(new CodeElement(trim($dependenciesExport)));
        }

        foreach ($this->mixins->getRequirementsStates() as $key => $value) {
            if ($value || $this->mixinsAllRequired || in_array($key, $this->mixinsPreCalled)) {
                $dependencies .= $this->mixins->get($key);
            }
        }

        return $dependencies;
    }

    /**
     * @return DependencyInjection
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param string $name dependency name
     *
     * @return string
     */
    public function getDependencyStorage($name)
    {
        $dependencyStorage = $this->dependencies->getStorageItem($name, $this->getOption('dependencies_storage'));

        $event = new DependencyStorageEvent($dependencyStorage);
        $this->trigger($event);

        return $event->getDependencyStorage();
    }

    /**
     * Entry point of the Formatter, typically waiting for a DocumentElement and
     * a format, to return a string with HTML and PHP nested.
     *
     * @param ElementInterface     $element
     * @param FormatInterface|null $format
     *
     * @return string
     */
    public function format(ElementInterface $element, $format = null)
    {
        if ($element instanceof DoctypeElement) {
            $formats = $this->getOption('formats');
            $doctype = $element->getValue();
            $this->setFormat($doctype);
            if (isset($formats[$doctype])) {
                $element->setValue(null);
            }
        }

        $format = $this->getFormatInstance($format);
        $format->setFormatter($this);

        $formatEvent = new FormatEvent($element, $format);
        $this->trigger($formatEvent);

        $element = $formatEvent->getElement();
        $format = $formatEvent->getFormat();

        $stringifyEvent = new StringifyEvent($formatEvent, $element ? $format($element) : '');
        $this->trigger($stringifyEvent);

        return $stringifyEvent->getOutput();
    }

    public function getModuleBaseClassName()
    {
        return FormatterModuleInterface::class;
    }
}

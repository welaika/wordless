<?php

namespace Phug;

// Node compilers
use Phug\Compiler\Element\BlockElement;
use Phug\Compiler\Event\CompileEvent;
use Phug\Compiler\Event\ElementEvent;
use Phug\Compiler\Event\NodeEvent;
use Phug\Compiler\Event\OutputEvent;
use Phug\Compiler\Layout;
use Phug\Compiler\Locator\FileLocator;
use Phug\Compiler\LocatorInterface;
use Phug\Compiler\NodeCompiler\AssignmentListNodeCompiler;
use Phug\Compiler\NodeCompiler\AssignmentNodeCompiler;
use Phug\Compiler\NodeCompiler\AttributeListNodeCompiler;
use Phug\Compiler\NodeCompiler\AttributeNodeCompiler;
use Phug\Compiler\NodeCompiler\BlockNodeCompiler;
use Phug\Compiler\NodeCompiler\CaseNodeCompiler;
use Phug\Compiler\NodeCompiler\CodeNodeCompiler;
use Phug\Compiler\NodeCompiler\CommentNodeCompiler;
use Phug\Compiler\NodeCompiler\ConditionalNodeCompiler;
use Phug\Compiler\NodeCompiler\DoctypeNodeCompiler;
use Phug\Compiler\NodeCompiler\DocumentNodeCompiler;
use Phug\Compiler\NodeCompiler\DoNodeCompiler;
use Phug\Compiler\NodeCompiler\EachNodeCompiler;
use Phug\Compiler\NodeCompiler\ElementNodeCompiler;
use Phug\Compiler\NodeCompiler\ExpressionNodeCompiler;
use Phug\Compiler\NodeCompiler\FilterNodeCompiler;
use Phug\Compiler\NodeCompiler\ForNodeCompiler;
use Phug\Compiler\NodeCompiler\ImportNodeCompiler;
use Phug\Compiler\NodeCompiler\KeywordNodeCompiler;
use Phug\Compiler\NodeCompiler\MixinCallNodeCompiler;
use Phug\Compiler\NodeCompiler\MixinNodeCompiler;
use Phug\Compiler\NodeCompiler\TextNodeCompiler;
use Phug\Compiler\NodeCompiler\VariableNodeCompiler;
use Phug\Compiler\NodeCompiler\WhenNodeCompiler;
use Phug\Compiler\NodeCompiler\WhileNodeCompiler;
// Nodes
use Phug\Compiler\NodeCompiler\YieldNodeCompiler;
use Phug\Compiler\NodeCompilerInterface;
use Phug\Compiler\Util\YieldHandlerTrait;
use Phug\Formatter\AbstractElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\AssignmentListNode;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeListNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\Node\CaseNode;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\Node\DoctypeNode;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\Node\DoNode;
use Phug\Parser\Node\EachNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\ForNode;
use Phug\Parser\Node\ImportNode;
use Phug\Parser\Node\KeywordNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\Node\WhileNode;
use Phug\Parser\Node\YieldNode;
use Phug\Parser\NodeInterface;
// Utils
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\ModuleContainerTrait;
use Phug\Util\SourceLocation;

class Compiler implements ModuleContainerInterface, CompilerInterface
{
    use ModuleContainerTrait;
    use YieldHandlerTrait;

    /**
     * @var array<Formatter>
     */
    private $formatters;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $importPaths;

    /**
     * @var array
     */
    private $nodeCompilers;

    /**
     * @var array
     */
    private $namedCompilers;

    /**
     * @var array[array[Block]]
     */
    private $namedBlocks;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var NodeInterface
     */
    private $currentNode;

    /**
     * @var CompilerInterface
     */
    private $parentCompiler;

    public function __construct($options = null)
    {
        $this->setOptionsDefaults($options ?: [], [
            'paths'                => [],
            'debug'                => false,
            'default_tag'          => 'div',
            'default_doctype'      => 'html',
            'extensions'           => ['', '.pug', '.jade'],
            'get_file_contents'    => 'file_get_contents',
            'on_compile'           => null,
            'on_output'            => null,
            'on_node'              => null,
            'on_element'           => null,
            'filename'             => null,
            'filters'              => [],
            'filter_resolvers'     => [],
            'includes'             => [],
            'parser_class_name'    => Parser::class,
            'formatter_class_name' => Formatter::class,
            'locator_class_name'   => FileLocator::class,
            'compiler_modules'     => [],
            'node_compilers'       => [
                AssignmentListNode::class  => AssignmentListNodeCompiler::class,
                AssignmentNode::class      => AssignmentNodeCompiler::class,
                AttributeListNode::class   => AttributeListNodeCompiler::class,
                AttributeNode::class       => AttributeNodeCompiler::class,
                BlockNode::class           => BlockNodeCompiler::class,
                YieldNode::class           => YieldNodeCompiler::class,
                CaseNode::class            => CaseNodeCompiler::class,
                CodeNode::class            => CodeNodeCompiler::class,
                CommentNode::class         => CommentNodeCompiler::class,
                ConditionalNode::class     => ConditionalNodeCompiler::class,
                DoctypeNode::class         => DoctypeNodeCompiler::class,
                DocumentNode::class        => DocumentNodeCompiler::class,
                DoNode::class              => DoNodeCompiler::class,
                EachNode::class            => EachNodeCompiler::class,
                KeywordNode::class         => KeywordNodeCompiler::class,
                ElementNode::class         => ElementNodeCompiler::class,
                ExpressionNode::class      => ExpressionNodeCompiler::class,
                FilterNode::class          => FilterNodeCompiler::class,
                ForNode::class             => ForNodeCompiler::class,
                ImportNode::class          => ImportNodeCompiler::class,
                MixinCallNode::class       => MixinCallNodeCompiler::class,
                MixinNode::class           => MixinNodeCompiler::class,
                TextNode::class            => TextNodeCompiler::class,
                VariableNode::class        => VariableNodeCompiler::class,
                WhenNode::class            => WhenNodeCompiler::class,
                WhileNode::class           => WhileNodeCompiler::class,
            ],
        ]);

        //Initialize parser to parse source code into an AST
        $parserClassName = $this->getOption('parser_class_name');

        if (!is_a($parserClassName, Parser::class, true)) {
            throw new \InvalidArgumentException(
                "Passed parser class $parserClassName is ".
                'not a valid '.Parser::class
            );
        }

        $this->parser = new $parserClassName($this->getOptions());

        //Initialize the formatter to turn elements into PHTML
        $this->initializeFormatter();

        //Initialize the Locator to locate sources
        $locatorClassName = $this->getOption('locator_class_name');

        if (!is_a($locatorClassName, LocatorInterface::class, true)) {
            throw new \InvalidArgumentException(
                "Passed locator class $locatorClassName is ".
                'not a valid '.LocatorInterface::class
            );
        }

        $this->locator = new $locatorClassName($this->getOptions());

        $this->nodeCompilers = [];
        $this->namedCompilers = [];
        $this->importPaths = [];
        $this->parentCompiler = null;

        foreach ($this->getOption('node_compilers') as $className => $handler) {
            $this->setNodeCompiler($className, $handler);
        }

        if ($onCompile = $this->getOption('on_compile')) {
            $this->attach(CompilerEvent::COMPILE, $onCompile);
        }

        if ($onOutput = $this->getOption('on_output')) {
            $this->attach(CompilerEvent::OUTPUT, $onOutput);
        }

        if ($onNode = $this->getOption('on_node')) {
            $this->attach(CompilerEvent::NODE, $onNode);
        }

        if ($onElement = $this->getOption('on_element')) {
            $this->attach(CompilerEvent::ELEMENT, $onElement);
        }

        $this->addModules($this->getOption('compiler_modules'));
    }

    private function initializeFormatter()
    {
        $formatterClassName = $this->getOption('formatter_class_name');

        if (!is_a($formatterClassName, Formatter::class, true)) {
            throw new \InvalidArgumentException(
                "Passed formatter class $formatterClassName is ".
                'not a valid '.Formatter::class
            );
        }

        $debug = $this->getOption('debug');
        $this->formatters[$debug] = new $formatterClassName($this->getOptions());

        return $this->formatters[$debug];
    }

    /**
     * Reset layout and compilers cache on clone.
     */
    public function __clone()
    {
        $this->layout = null;
        $this->namedCompilers = [];
        $this->importPaths = [];
        $this->importNodeYielded = false;
        $this->importNode = null;
        $this->parentCompiler = null;
    }

    public function getParentCompiler()
    {
        return $this->parentCompiler;
    }

    public function setParentCompiler(CompilerInterface $compiler)
    {
        $this->parentCompiler = $compiler;

        return $this;
    }

    /**
     * Locate a file for a given path. Returns null if
     * not found.
     *
     * @param string $path
     * @param array  $paths
     *
     * @return string|null
     */
    public function locate($path, $paths = null)
    {
        $paths = $paths ?: $this->getOption('paths');

        return $this->locator->locate(
            $path,
            $paths,
            $this->getOption('extensions')
        );
    }

    /**
     * Resolve a path using the base directories. Throw
     * an exception if not found.
     *
     * @param string $path
     * @param array  $paths
     *
     * @throws CompilerException
     *
     * @return string
     */
    public function resolve($path, $paths = null)
    {
        $resolvePath = $this->locate($path, $paths);

        $this->assert(
            $resolvePath || $this->hasOption('not_found_template'),
            sprintf(
                "Source file %s not found \nPaths: %s \nExtensions: %s",
                $path,
                implode(', ', $this->getOption('paths')),
                implode(', ', $this->getOption('extensions'))
            )
        );

        return $resolvePath;
    }

    /**
     * Return the contents for a given file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents($path)
    {
        if ($path) {
            return call_user_func($this->getOption('get_file_contents'), $path);
        }

        return $this->getOption('not_found_template');
    }

    /**
     * Return the current layout extended if set.
     *
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the current layout to extend.
     *
     * @param Layout $layout layout extended
     *
     * @return $this
     */
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Returns the currently used Parser instance.
     *
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Returns the currently used Formatter instance.
     *
     * @return Formatter
     */
    public function getFormatter()
    {
        $debug = $this->getOption('debug');

        return isset($this->formatters[$debug])
            ? $this->formatters[$debug]
            : $this->initializeFormatter();
    }

    /**
     * Set the node compiler for a given node class name.
     *
     * @param string                       $className node class name
     * @param NodeCompilerInterface|string $handler   handler
     *
     * @return $this
     */
    public function setNodeCompiler($className, $handler)
    {
        if (!is_subclass_of($handler, NodeCompilerInterface::class)) {
            throw new \InvalidArgumentException(
                'Passed node compiler needs to implement '.
                NodeCompilerInterface::class.'. '.
                (is_object($handler) ? get_class($handler) : $handler).
                ' given.'
            );
        }

        $this->nodeCompilers[$className] = $handler;

        return $this;
    }

    /**
     * Create a new compiler instance by name or return the previous
     * instance with the same name.
     *
     * @param string $compiler name
     *
     * @return NodeCompilerInterface
     */
    private function getNamedCompiler($compiler)
    {
        if (!isset($this->namedCompilers[$compiler])) {
            $this->namedCompilers[$compiler] = new $compiler($this);
        }

        return $this->namedCompilers[$compiler];
    }

    /**
     * Return list of blocks for a given name.
     *
     * @param $name
     *
     * @return mixed
     */
    public function &getBlocksByName($name)
    {
        if (!isset($this->namedBlocks[$name])) {
            $this->namedBlocks[$name] = [];
        }

        return $this->namedBlocks[$name];
    }

    /**
     * Returns lists of blocks grouped by name.
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->namedBlocks;
    }

    /**
     * Returns PHTML from pug node input.
     *
     * @param NodeInterface    $node   input
     * @param ElementInterface $parent optional parent element
     *
     * @throws CompilerException
     *
     * @return ElementInterface
     */
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $nodeEvent = new NodeEvent($node);
        $this->trigger($nodeEvent);
        $node = $nodeEvent->getNode();

        $this->currentNode = $node;

        foreach ($this->nodeCompilers as $className => $compiler) {
            if (is_a($node, $className)) {
                if (!($compiler instanceof NodeCompilerInterface)) {
                    $compiler = $this->getNamedCompiler($compiler);
                }

                $element = $compiler->compileNode($node, $parent);

                if ($element instanceof ElementInterface && !($element instanceof BlockElement)) {
                    $elementEvent = new ElementEvent($nodeEvent, $element);
                    $this->trigger($elementEvent);
                    $element = $elementEvent->getElement();
                }

                return $element;
            }
        }

        $this->throwException(
            'No compiler found able to compile '.get_class($node),
            $node
        );
    }

    /**
     * Replace a block by its nodes.
     *
     * @param BlockElement $block
     * @param array        $children
     */
    public function replaceBlock(BlockElement $block, array $children = null)
    {
        if ($parent = $block->getParent()) {
            foreach (array_reverse($children ?: $block->getChildren()) as $child) {
                $parent->insertAfter($block, $child);
            }
            $previous = $block->getPreviousSibling();
            if ($previous instanceof TextElement) {
                $previous->setEnd(true);
            }
            $block->remove();
        }
    }

    /**
     * Replace each block by its compiled children.
     *
     * @throws CompilerException
     *
     * @return $this
     */
    public function compileBlocks()
    {
        do {
            $blockProceeded = 0;
            foreach ($this->getBlocks() as $name => $blocks) {
                foreach ($blocks as $block) {
                    $this->assert(
                        $block instanceof BlockElement,
                        'Unexpected block for the name '.$name,
                        $block instanceof AbstractElement ? $block->getOriginNode() : null
                    );
                    /** @var BlockElement $block */
                    if ($block->hasParent()) {
                        $this->replaceBlock($block);
                        $blockProceeded++;
                    }
                }
            }
        } while ($blockProceeded);

        return $this;
    }

    /**
     * Import blocks named lists into the compiler.
     *
     * @param array $blocks
     *
     * @return $this
     */
    public function importBlocks(array $blocks)
    {
        foreach ($blocks as $name => $list) {
            foreach ($list as $block) {
                /* @var BlockElement $block */
                $block->addCompiler($this);
            }
        }

        return $this;
    }

    /**
     * Register a path as an imported path of the current
     * compiling path.
     *
     * @param $path
     */
    public function registerImportPath($path)
    {
        $current = $this->getPath();
        if (!isset($this->importPaths[$current])) {
            $this->importPaths[$current] = [];
        }

        $this->importPaths[$current][] = $path;

        if ($this->parentCompiler) {
            $this->parentCompiler->registerImportPath($path);
        }

        return $this;
    }

    /**
     * @param string|null $path
     *
     * @return array
     */
    public function getImportPaths($path = null)
    {
        if ($path === null) {
            return $this->importPaths;
        }

        return isset($this->importPaths[$path])
            ? $this->importPaths[$path]
            : [];
    }

    /**
     * @return array
     */
    public function getCurrentImportPaths()
    {
        return $this->getImportPaths($this->getPath());
    }

    /**
     * Dump a debug tre for a given pug input.
     *
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @return string
     */
    public function dump($input, $path = null)
    {
        $element = $this->compileDocument($input, $path);

        return $element instanceof ElementInterface
            ? $element->dump()
            : var_export($element, true);
    }

    /**
     * Dump a debug tre for a given pug input.
     *
     * @param string $path pug input file
     *
     * @return string
     */
    public function dumpFile($path)
    {
        $path = $this->resolve($path);

        return $this->dump($this->getFileContents($path), $path);
    }

    /**
     * Returns ElementInterface from pug input with all layouts and
     * blocks compiled.
     *
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @throws CompilerException
     *
     * @return null|ElementInterface
     */
    public function compileDocument($input, $path = null)
    {
        $element = $this->compileIntoElement($input, $path);
        $layout = $this->getLayout();
        $blocksCompiler = $this;
        if ($layout) {
            $element = $layout->getDocument();
            $blocksCompiler = $layout->getCompiler();
        }
        $blocksCompiler->compileBlocks();

        return $element;
    }

    /**
     * Returns PHTML from pug input.
     *
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @return string
     */
    public function compile($input, $path = null)
    {
        $compileEvent = new CompileEvent($input, $path ?: $this->getOption('filename'));
        $this->trigger($compileEvent);

        $input = $compileEvent->getInput();
        $path = $compileEvent->getPath();
        $this->importPaths = [];
        $includes = [];
        foreach ($this->getOption('includes') as $include) {
            $includes[] = $this->compileDocument(file_get_contents($include), $include);
        }

        $element = $this->compileDocument($input, $path);

        foreach (array_reverse($includes) as $include) {
            $element->prependChild($include);
        }

        $output = $this->getFormatter()->format($element);
        $output = $this->getFormatter()->formatDependencies().$output;

        $outputEvent = new OutputEvent($compileEvent, $output);
        $this->trigger($outputEvent);

        return $outputEvent->getOutput();
    }

    /**
     * Returns PHTML from pug input file.
     *
     * @param string $path path of the compiled source
     *
     * @return string
     */
    public function compileFile($path)
    {
        $path = $this->resolve($path);

        return $this->compile($this->getFileContents($path), $path);
    }

    /**
     * Returns ElementInterface from pug input.
     *
     * @param string $input pug input
     * @param string $path  optional path of the compiled source
     *
     * @throws \Exception
     *
     * @return null|ElementInterface
     */
    public function compileIntoElement($input, $path = null)
    {
        $this->path = $path;
        $this->namedBlocks = [];

        $node = $this->parser->parse($input, $path); //Let exceptions fall through

        $element = $this->compileNode($node);

        if ($element && !($element instanceof ElementInterface)) {
            $this->throwException(
                get_class($node).
                ' compiled into a value that does not implement ElementInterface: '.
                (is_object($element) ? get_class($element) : gettype($element)),
                $node
            );
        } // @codeCoverageIgnore

        return $element;
    }

    /**
     * Returns ElementInterface from pug input file.
     *
     * @param string $path path of the compiled source
     *
     * @return ElementInterface
     */
    public function compileFileIntoElement($path)
    {
        $path = $this->resolve($path);

        return $this->compileIntoElement($this->getFileContents($path), $path);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path ?: $this->getOption('filename');
    }

    public function getModuleBaseClassName()
    {
        return CompilerModuleInterface::class;
    }

    public function hasFilter($name)
    {
        return $this->getFilter($name) !== null;
    }

    public function getFilter($name)
    {
        $filters = $this->getOption('filters');

        if (isset($filters[$name])) {
            return $filters[$name];
        }

        foreach ($this->getOption('filter_resolvers') ?: [] as $resolver) {
            $filter = call_user_func($resolver, $name);
            if ($filter !== null) {
                return $filter;
            }
        }

        return null;
    }

    public function setFilter($name, $filter)
    {
        return $this->setOption(['filters', $name], $filter);
    }

    public function unsetFilter($name)
    {
        return $this->unsetOption(['filters', $name]);
    }

    /**
     * Throws a compiler-exception.
     *
     * The current file, line and offset of the exception
     * get automatically appended to the exception
     *
     * @param string        $message  A meaningful error message
     * @param NodeInterface $node     Node generating the error
     * @param int           $code     Error code
     * @param \Throwable    $previous Source error
     *
     * @throws CompilerException
     */
    public function throwException($message, $node = null, $code = 0, $previous = null)
    {
        $pattern = "Failed to compile: %s \nLine: %s \nOffset: %s";

        $location = $node ? $node->getSourceLocation() : null;

        $path = $location ? $location->getPath() : $this->getPath();
        $line = $location ? $location->getLine() : 0;
        $offset = $location ? $location->getOffset() : 0;
        $offsetLength = $location ? $location->getOffsetLength() : 0;

        if ($path) {
            $pattern .= "\nPath: $path";
        }

        throw new CompilerException(
            new SourceLocation($path, $line, $offset, $offsetLength),
            vsprintf($pattern, [
                $message,
                $line,
                $offset,
            ]),
            $code,
            $previous
        );
    }

    public function assert($condition, $message, $node = null, $code = 0, $previous = null)
    {
        if (!$condition) {
            $this->throwException($message, $node, $code, $previous);
        } // @codeCoverageIgnore
    }
}

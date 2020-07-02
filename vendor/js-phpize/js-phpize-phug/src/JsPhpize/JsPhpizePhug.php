<?php

namespace JsPhpize;

use Exception;
use JsPhpize\Traits\Compilation;
use Phug\AbstractCompilerModule;
use Phug\Compiler;
use Phug\Compiler\Event\NodeEvent;
use Phug\CompilerEvent;
use Phug\Formatter\Element\KeywordElement;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\KeywordNode;
use Phug\Parser\Node\TextNode;
use Phug\Renderer;
use Phug\Util\ModuleContainerInterface;

class JsPhpizePhug extends AbstractCompilerModule
{
    use Compilation;

    protected $languages = ['js', 'php'];

    public function __construct(ModuleContainerInterface $container)
    {
        parent::__construct($container);

        if ($container instanceof Renderer) {
            return;
        }

        /* @var Compiler $compiler */
        $compiler = $container;

        //Make sure we can retrieve the module options from the container
        $compiler->setOptionsRecursive([
            'module_options' => [
                'jsphpize' => [],
            ],
        ]);

        //Set default options
        $this->setOptionsRecursive([
            'language' => 'js',
            'allowTruncatedParentheses' => true,
            'catchDependencies' => true,
            'ignoreDollarVariable' => true,
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
        ]);

        //Apply options from container
        $this->setOptionsRecursive($compiler->getOption(['module_options', 'jsphpize']));

        $compiler->attach(CompilerEvent::NODE, [$this, 'handleNodeEvent']);

        $compiler->setOptionsRecursive([
            'keywords' => [
                'language' => [$this, 'handleLanguageKeyword'],
                'node-language' => [$this, 'handleNodeLanguageKeyword'],
            ],
            'patterns' => [
                'transform_expression' => function ($code) use ($compiler) {
                    return $this->transformExpression($this->getJsPhpizeEngine($compiler), $code, $compiler->getPath());
                },
            ],
            'checked_variable_exceptions' => [
                'js-phpize' => [static::class, 'checkedVariableExceptions'],
            ],
        ]);
    }

    public function handleNodeEvent(NodeEvent $event)
    {
        /* @var CommentNode $node */
        if (($node = $event->getNode()) instanceof CommentNode &&
            !$node->isVisible() &&
            $node->hasChildAt(0) &&
            ($firstChild = $node->getChildAt(0)) instanceof TextNode &&
            preg_match(
                '/^@((?:node-)?lang(?:uage)?)([\s(].*)$/',
                trim($firstChild->getValue()),
                $match
            )
        ) {
            $keyword = new KeywordNode(
                $node->getToken(),
                $node->getSourceLocation(),
                $node->getLevel(),
                $node->getParent()
            );
            $keyword->setName($match[1]);
            $keyword->setValue($match[2]);
            $event->setNode($keyword);
        }
    }

    protected function getLanguageKeywordValue($value, KeywordElement $keyword, $name)
    {
        $value = trim($value, "()\"' \t\n\r\0\x0B");

        if (!in_array($value, $this->languages)) {
            $file = 'unknown';
            $line = 'unknown';
            $offset = 'unknown';
            $node = $keyword->getOriginNode();
            if ($node && ($location = $node->getSourceLocation())) {
                $file = $location->getPath();
                $line = $location->getLine();
                $offset = $location->getOffset();
            }

            throw new \InvalidArgumentException(sprintf(
                "Invalid argument for %s keyword: %s. Possible values are: %s\nFile: %s\nLine: %s\nOffset:%s",
                $name,
                $value,
                implode(', ', $this->languages),
                $file,
                $line,
                $offset
            ));
        }

        return $value;
    }

    public function handleNodeLanguageKeyword($value, KeywordElement $keyword, $name)
    {
        $value = $this->getLanguageKeywordValue($value, $keyword, $name);

        if ($next = $keyword->getNextSibling()) {
            $next->prependChild(new KeywordElement('language', $value, $keyword->getOriginNode()));
            $next->appendChild(new KeywordElement('language', $this->getOption('language'), $keyword->getOriginNode()));
        }

        return '';
    }

    public function handleLanguageKeyword($value, KeywordElement $keyword, $name)
    {
        $value = $this->getLanguageKeywordValue($value, $keyword, $name);

        $this->setOption('language', $value);

        return '';
    }

    protected function transformExpression(JsPhpize $jsPhpize, $code, $fileName)
    {
        if ($this->getOption('language') === 'php') {
            return $code;
        }

        $compilation = $this->compile($jsPhpize, $code, $fileName);

        if (!($compilation instanceof Exception)) {
            return $compilation;
        }

        return $code;
    }

    public static function checkedVariableExceptions($variable, $index, $tokens)
    {
        return $index > 2 &&
            $tokens[$index - 1] === '(' &&
            $tokens[$index - 2] === ']' &&
            !preg_match('/^__?pug_/', $variable) &&
            is_array($tokens[$index - 3]) &&
            $tokens[$index - 3][0] === T_CONSTANT_ENCAPSED_STRING &&
            preg_match('/_with_ref\'$/', $tokens[$index - 3][1]);
    }
}

<?php

namespace Phug\Formatter;

use Phug\Formatter;
use Phug\Formatter\Element\AbstractValueElement;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\CommentElement;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\KeywordElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\Element\MixinElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\Element\VariableElement;
use Phug\Formatter\Partial\HandleVariable;
use Phug\Formatter\Partial\PatternTrait;
use Phug\Formatter\Util\PhpUnwrap;
use Phug\FormatterException;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\NodeInterface;
use Phug\Util\OptionInterface;
use Phug\Util\Partial\OptionTrait;
use Phug\Util\SourceLocation;
use SplObjectStorage;

abstract class AbstractFormat implements FormatInterface, OptionInterface
{
    use HandleVariable;
    use OptionTrait;
    use PatternTrait;

    const CLASS_ATTRIBUTE = '(is_array($_pug_temp = %s) ? implode(" ", $_pug_temp) : strval($_pug_temp))';
    const STRING_ATTRIBUTE = '
        (is_array($_pug_temp = %s) || is_object($_pug_temp) && !method_exists($_pug_temp, "__toString")
            ? json_encode($_pug_temp)
            : strval($_pug_temp))';
    const EXPRESSION_IN_TEXT = '(is_bool($_pug_temp = %s) ? var_export($_pug_temp, true) : $_pug_temp)';
    const HTML_EXPRESSION_ESCAPE = 'htmlspecialchars(%s)';
    const HTML_TEXT_ESCAPE = 'htmlspecialchars';
    const PAIR_TAG = '%s%s%s';
    const TRANSFORM_EXPRESSION = '%s';
    const TRANSFORM_CODE = '%s';
    const TRANSFORM_RAW_CODE = '%s';
    const PHP_HANDLE_CODE = '<?php %s ?>';
    const PHP_BLOCK_CODE = ' {%s}';
    const PHP_NESTED_HTML = ' ?>%s<?php ';
    const PHP_DISPLAY_CODE = '<?= %s ?>';
    const DISPLAY_COMMENT = '<!-- %s -->';
    const DOCTYPE = '';
    const CUSTOM_DOCTYPE = '<!DOCTYPE %s>';
    const SAVE_VALUE = '%s=%s';
    const DEBUG_COMMENT = "\n// PUG_DEBUG:%s\n";

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var string
     */
    private $debugCommentPattern = null;

    public function __construct(Formatter $formatter = null)
    {
        $patterns = [
            'class_attribute'        => static::CLASS_ATTRIBUTE,
            'string_attribute'       => static::STRING_ATTRIBUTE,
            'expression_in_text'     => static::EXPRESSION_IN_TEXT,
            'html_expression_escape' => static::HTML_EXPRESSION_ESCAPE,
            'html_text_escape'       => static::HTML_TEXT_ESCAPE,
            'pair_tag'               => static::PAIR_TAG,
            'transform_expression'   => static::TRANSFORM_EXPRESSION,
            'transform_code'         => static::TRANSFORM_CODE,
            'transform_raw_code'     => static::TRANSFORM_RAW_CODE,
            'php_handle_code'        => static::PHP_HANDLE_CODE,
            'php_display_code'       => static::PHP_DISPLAY_CODE,
            'php_block_code'         => static::PHP_BLOCK_CODE,
            'php_nested_html'        => static::PHP_NESTED_HTML,
            'display_comment'        => static::DISPLAY_COMMENT,
            'doctype'                => static::DOCTYPE,
            'custom_doctype'         => static::CUSTOM_DOCTYPE,
            'debug_comment'          => static::DEBUG_COMMENT,
            'debug'                  => function ($nodeId) {
                return $this->handleCode($this->getDebugComment($nodeId));
            },
        ];
        $formatter = $formatter ?: new Formatter();
        if (!$formatter->getOption('debug')) {
            foreach ($patterns as &$pattern) {
                if (is_string($pattern) && mb_substr($pattern, 0, 1) === "\n") {
                    $pattern = preg_replace('/\s+/', ' ', trim($pattern));
                }
            }
        }
        $this
            ->setOptionsRecursive([
                'debug'              => true,
                'short_open_tag_fix' => 'auto',
                'pattern'            => function ($pattern) {
                    $args = func_get_args();
                    $args[0] = $pattern;
                    $function = 'sprintf';
                    if (is_callable($pattern)) {
                        $function = $pattern;
                        $args = array_slice($args, 1);
                    }

                    return call_user_func_array($function, $args);
                },
                'patterns'         => $patterns,
                'pretty'           => false,
                'element_handlers' => [
                    AssignmentElement::class => [$this, 'formatAssignmentElement'],
                    AttributeElement::class  => [$this, 'formatAttributeElement'],
                    CodeElement::class       => [$this, 'formatCodeElement'],
                    CommentElement::class    => [$this, 'formatCommentElement'],
                    ExpressionElement::class => [$this, 'formatExpressionElement'],
                    DoctypeElement::class    => [$this, 'formatDoctypeElement'],
                    DocumentElement::class   => [$this, 'formatDocumentElement'],
                    KeywordElement::class    => [$this, 'formatKeywordElement'],
                    MarkupElement::class     => [$this, 'formatMarkupElement'],
                    MixinCallElement::class  => [$this, 'formatMixinCallElement'],
                    MixinElement::class      => [$this, 'formatMixinElement'],
                    TextElement::class       => [$this, 'formatTextElement'],
                    VariableElement::class   => [$this, 'formatVariableElement'],
                ],
                'php_token_handlers' => [
                    T_VARIABLE => [$this, 'handleVariable'],
                ],
                'mixin_merge_mode' => 'replace',
            ])
            ->setFormatter($formatter)
            ->registerHelper('pattern', $this->getOption('pattern'))
            ->addPatterns($this->getOption('patterns'));

        $this->debugCommentPattern = trim($this->getDebugComment(''));
    }

    /**
     * @param Formatter $formatter
     *
     * @return $this
     */
    public function setFormatter(Formatter $formatter)
    {
        $this->formatter = $formatter;
        $format = $this;

        return $this
            ->setOptionsRecursive($formatter->getOptions())
            ->registerHelper(
                'dependencies_storage',
                $formatter->getOption('dependencies_storage')
            )->registerHelper(
                'helper_prefix',
                static::class.'::'
            )->provideHelper(
                'get_helper',
                [
                    'dependencies_storage',
                    'helper_prefix',
                    function ($dependenciesStorage, $prefix) use ($format) {
                        return function ($name) use ($dependenciesStorage, $prefix, $format) {
                            if (!isset($$dependenciesStorage)) {
                                return $format->getHelper($name);
                            }

                            $storage = $$dependenciesStorage;

                            if (!array_key_exists($prefix.$name, $storage) &&
                                !isset($storage[$prefix.$name])
                            ) {
                                throw new \Exception(
                                    var_export($name, true).
                                    ' dependency not found in the namespace: '.
                                    var_export($prefix, true)
                                );
                            }

                            return $storage[$prefix.$name];
                        };
                    },
                ]
            );
    }

    public function getDebugComment($nodeId)
    {
        return $this->pattern(
            'debug_comment',
            $nodeId
        );
    }

    protected function getDebugInfo($element)
    {
        /* @var NodeInterface $node */
        $node = null;

        if (!(
            $element instanceof ElementInterface &&
            ($node = $element->getOriginNode())
        ) ||
        $node instanceof WhenNode || (
            $node instanceof ConditionalNode &&
            $node->getName() === 'else'
        )) {
            return '';
        }

        return $this->pattern(
            'debug',
            $this->formatter->storeDebugNode($node)
        );
    }

    /**
     * @param string|ElementInterface $element
     * @param bool                    $noDebug
     * @param $element
     *
     * @return string
     */
    public function format($element, $noDebug = false)
    {
        if (is_string($element)) {
            return $element;
        }

        $debug = $this->getOption('debug') && !$noDebug;
        foreach ($this->getOption('element_handlers') as $className => $handler) {
            if (is_a($element, $className)) {
                $elementCode = $handler($element);
                $debugCode = $debug ? $this->getDebugInfo($element) : '';
                $glue = mb_strlen($debugCode) && in_array(mb_substr($elementCode, 0, 1), ["\n", "\r"])
                        ? "\n"
                        : '';

                return $debugCode.$glue.$elementCode;
            }
        }

        return '';
    }

    /**
     * @param $className
     *
     * @return $this
     */
    public function removeElementHandler($className)
    {
        return $this->unsetOption(['element_handlers', $className]);
    }

    /**
     * @param          $className
     * @param callable $handler
     *
     * @return $this
     */
    public function setElementHandler($className, callable $handler)
    {
        return $this->setOption(['element_handlers', $className], $handler);
    }

    /**
     * @param $phpTokenId
     *
     * @return $this
     */
    public function removePhpTokenHandler($phpTokenId)
    {
        return $this->unsetOption(['php_token_handlers', $phpTokenId]);
    }

    /**
     * @param $phpTokenId
     * @param $handler
     *
     * @return $this
     */
    public function setPhpTokenHandler($phpTokenId, $handler)
    {
        return $this->setOption(['php_token_handlers', $phpTokenId], $handler);
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
        return $this->pattern('php_handle_code', $phpCode);
    }

    protected function getNewLine()
    {
        $pretty = $this->getOption('pretty');

        return $pretty || $pretty === '' ? PHP_EOL : '';
    }

    protected function getIndent()
    {
        $pretty = $this->getOption('pretty');
        if (!$pretty) {
            return '';
        }

        return str_repeat(is_string($pretty) ? $pretty : '  ', $this->formatter->getLevel());
    }

    protected function pattern($patternOption)
    {
        $args = func_get_args();
        $args[0] = $this->patternName($patternOption);

        return call_user_func_array([$this, 'callHelper'], $args);
    }

    protected function handleTokens($code, $checked)
    {
        $phpTokenHandler = $this->getOption('php_token_handlers');
        $tokens = array_slice(token_get_all('<?php '.$code), 1);
        $afterIsset = false;
        $inIsset = false;

        foreach ($tokens as $index => $token) {
            $tokenId = $token;
            $text = $token;
            if ($afterIsset && $token === ')') {
                $inIsset = false;
                $afterIsset = false;
            }
            if ($afterIsset && $token === '(') {
                $inIsset = true;
            }
            if (is_array($token) && $token[0] === T_ISSET) {
                $afterIsset = true;
            }
            if (!is_string($tokenId)) {
                list($tokenId, $text) = $token;
            }
            if (!isset($phpTokenHandler[$tokenId])) {
                yield $text;

                continue;
            }
            if (is_string($phpTokenHandler[$tokenId])) {
                yield sprintf($phpTokenHandler[$tokenId], $text);

                continue;
            }

            yield $phpTokenHandler[$tokenId]($text, $index, $tokens, $checked && !$inIsset);
        }
    }

    /**
     * Apply html_expression_escape pattern.
     *
     * @param string $expression
     *
     * @return string
     */
    public function escapeHtml($expression)
    {
        return $this->pattern('html_expression_escape', $expression);
    }

    /**
     * Format a code with transform_expression and tokens handlers.
     *
     * @param string $code
     * @param bool   $checked
     * @param bool   $noTransformation
     *
     * @return string
     */
    public function formatCode($code, $checked, $noTransformation = false)
    {
        if (!$noTransformation) {
            $code = $this->pattern(
                'transform_code',
                $this->pattern(
                    'transform_expression',
                    $this->pattern('transform_raw_code', $code)
                )
            );
        }

        return implode('', iterator_to_array($this->handleTokens(
            $code,
            $checked
        )));
    }

    protected function formatAssignmentValue($value)
    {
        if ($value instanceof ExpressionElement) {
            return $this->formatCode($value->getValue(), $value->isChecked());
        }

        return var_export(strval($this->format($value, true)), true);
    }

    protected function formatDynamicValue($formattedName, $value)
    {
        if ($value instanceof ExpressionElement &&
            strtolower($value->getValue()) === 'undefined'
        ) {
            return 'null';
        }

        if ($value instanceof ExpressionElement &&
            in_array(($code = strtolower($value->getValue())), ['true', 'false', 'null', 'undefined'])
        ) {
            return $code;
        }

        $code = $this->formatAssignmentValue($value);
        if ($value instanceof ExpressionElement && $value->isEscaped()) {
            return $this->exportHelper('array_escape', [$formattedName, $code]);
        }

        return $code;
    }

    protected function formatPairAsArrayItem($name, $value)
    {
        $formattedName = $this->formatAssignmentValue($name);
        $code = $this->formatDynamicValue($formattedName, $value);

        return '['.$formattedName.' => '.$code.']';
    }

    protected function formatAttributeAsArrayItem(AttributeElement $attribute)
    {
        return $this->formatPairAsArrayItem($attribute->getName(), $attribute->getValue());
    }

    protected function arrayToPairsExports($array)
    {
        $exports = [];
        foreach ($array as $attribute) {
            $exports[] = $this->formatAttributeAsArrayItem($attribute);
        }

        return $exports;
    }

    protected function attributesAssignmentsFromPairs($pairs, $helper = 'attributes_assignment')
    {
        $expression = new ExpressionElement($this->exportHelper($helper, $pairs));
        $expression->uncheck();
        $expression->preventFromTransformation();

        return $expression;
    }

    /**
     * @param array $attributes
     *
     * @return ExpressionElement
     */
    public function formatAttributesList($attributes)
    {
        return $this->attributesAssignmentsFromPairs($this->arrayToPairsExports($attributes), 'merge_attributes');
    }

    /**
     * @param KeywordElement $element
     *
     * @return string
     */
    protected function formatKeywordElement(KeywordElement $element)
    {
        $name = $element->getName();
        $keyword = $this->getOption(['keywords', $name]);
        $result = call_user_func($keyword, $element->getValue(), $element, $name);

        if (is_string($result)) {
            $result = ['begin' => $result];
        }

        if (!is_array($result) && !($result instanceof \ArrayAccess)) {
            $this->throwException(
                "The keyword $name returned an invalid value type, string or array was expected.",
                $element
            );
        }

        foreach (['begin', 'end'] as $key) {
            $result[$key] = (isset($result[$key.'Php'])
                ? "<?php\n".$result[$key.'Php']."\n?>"
                : ''
            ).(isset($result[$key])
                ? $result[$key]
                : ''
            );
        }

        return implode('', array_filter([
            $result['begin'],
            $this->formatElementChildren($element),
            $result['end'],
        ]));
    }

    protected function formatVariableElement(VariableElement $element)
    {
        $variable = $this->formatCode($element->getVariable()->getValue(), false);
        $expression = $element->getExpression();
        $value = $this->formatCode($expression->getValue(), $expression->isChecked());
        if ($expression->isEscaped()) {
            $value = $this->escapeHtml($value);
        }

        return $this->handleCode($this->pattern('save_value', $variable, $value ?: 'null'));
    }

    protected function formatCodeElement(CodeElement $code)
    {
        $php = $this->formatCode($code->getValue(), false, !$code->isTransformationAllowed());

        if ($code->needAccolades()) {
            $php = preg_replace('/\s*\{\s*\}\s*$/', '', $php).$this->pattern(
                'php_block_code',
                $code->hasChildren()
                    ? $this->pattern('php_nested_html', $this->formatElementChildren($code, 0))
                    : ''
            );
        } elseif ($code->hasChildren()) {
            $php = preg_replace('/\s*\{\s*\}\s*$/', '', $php).
                $this->pattern('php_nested_html', $this->formatElementChildren($code, 0));
        }

        return $this->handleCode($php);
    }

    protected function formatCommentElement(CommentElement $element)
    {
        return $this->getIndent().
            $this->pattern('display_comment', $element->getValue()).
            $this->getNewLine();
    }

    protected function formatAttributeValueAccordingToName($value, $name, $checked)
    {
        if ($name instanceof ExpressionElement) {
            return $this->exportHelper('stand_alone_attribute_assignment', [
                $this->formatCode($name->getValue(), $checked),
                $value,
            ]);
        }

        if ($name === 'class') {
            return $this->exportHelper('stand_alone_class_attribute_assignment', [
                $value,
            ]);
        }

        if ($name === 'style') {
            return $this->exportHelper('stand_alone_style_attribute_assignment', [
                $value,
            ]);
        }

        return $this->pattern('string_attribute', $value, $this->formatCode($name, $checked));
    }

    protected function formatExpressionElement(ExpressionElement $code)
    {
        $value = $code->getValue();

        if ($code->hasStaticValue()) {
            $value = strval(eval('return '.$value.';'));
            if ($code->isEscaped()) {
                $value = $this->pattern('html_text_escape', $value);
            }

            return $value;
        }

        $value = $this->formatCode($value, $code->isChecked(), !$code->isTransformationAllowed());

        if ($link = $code->getLink()) {
            if ($link instanceof AttributeElement) {
                $value = $this->formatAttributeValueAccordingToName($value, $link->getName(), $code->isChecked());
            }
        }

        if (preg_match('/\/\/[^\n]*$/', $value)) {
            $value .= "\n";
        }

        if (!$link) {
            $value = $this->pattern('expression_in_text', $value);
        }

        if ($code->isEscaped()) {
            $value = $this->escapeHtml($value);
        }

        return $this->pattern('php_display_code', $value);
    }

    protected function formatTextElement(TextElement $text)
    {
        $value = $text->getValue();
        if ($text->isEscaped()) {
            $value = $this->pattern('html_text_escape', $value);
        }
        $previous = $text->getPreviousSibling();
        if ($previous instanceof TextElement && !$previous->isEnd() && trim($previous->getValue()) !== '') {
            $value = "\n".$value;
        }

        return $this->format($value);
    }

    protected function formatDoctypeElement(DoctypeElement $doctype)
    {
        $type = $doctype->getValue();
        $pattern = $type ? 'custom_doctype' : 'doctype';
        $code = $this->pattern($pattern, $type);
        $shortTagFix = $this->getOption('short_open_tag_fix');
        if ($shortTagFix === 'auto') {
            $shortTagFix = intval(ini_get('short_open_tag')) || intval(ini_get('hhvm.enable_short_tags'));
        }
        // @codeCoverageIgnoreStart
        if ($shortTagFix) {
            $code = preg_replace('/<\?(?!php)/', '<<?= "?" ?>', $code);
        }
        // @codeCoverageIgnoreEnd

        return $code.$this->getNewLine();
    }

    protected function formatMixinAttributeValue($value)
    {
        if ($value instanceof TextElement) {
            $value = var_export($value->getValue(), true);
        } elseif ($value instanceof AbstractValueElement) {
            $value = $value->getValue();
        }

        return $value;
    }

    protected function getMixinAttributes(SplObjectStorage $source)
    {
        $attributes = [];
        foreach ($source as $attribute) {
            /** @var AttributeElement $attribute */
            $defaultValue = '';
            if ($attribute->getValue()) {
                $value = $this->formatMixinAttributeValue($attribute->getValue());
                $defaultValue = ', '.$this->formatCode($value, true);
            }
            $attributes[] = '['.
                ($attribute->isVariadic() ? 'true' : 'false').', '.
                var_export(strval($attribute->getName()), true).
                $defaultValue.
                ']';
        }

        return '['.implode(', ', $attributes).']';
    }

    protected function formatMixinElement(MixinElement $mixin)
    {
        $mixinName = $mixin->getName();
        $name = is_string($mixinName)
            ? var_export($mixinName, true)
            : $this->formatter->formatCode($mixinName->getValue());
        $id = is_string($mixinName)
            ? $mixinName
            : uniqid($name.'_');
        $attributes = $this->getMixinAttributes($mixin->getAttributes());
        $children = new PhpUnwrap($this->formatElementChildren($mixin), $this->formatter);
        $variable = '$__pug_mixins['.$name.']';
        $mode = strtolower($this->getOption('mixin_merge_mode'));
        $mixinCode = $this->handleCode(implode("\n", [
            'if (!isset($__pug_mixins)) {',
            '    $__pug_mixins = [];',
            '}',
            ($mode === 'ignore' ? '!isset('.$variable.') && ' : '').
            $variable.' = function ('.
            '$block, $attributes, $__pug_arguments, $__pug_mixin_vars, $__pug_children'.
            ') use (&$__pug_mixins, &$'.$this->getOption('dependencies_storage').') {',
            '    $__pug_values = [];',
            '    foreach ($__pug_arguments as $__pug_argument) {',
            '        if ($__pug_argument[0]) {',
            '            foreach ($__pug_argument[1] as $__pug_value) {',
            '                $__pug_values[] = $__pug_value;',
            '            }',
            '            continue;',
            '        }',
            '        $__pug_values[] = $__pug_argument[1];',
            '    }',
            '    $__pug_attributes = '.$attributes.';',
            '    $__pug_names = [];',
            '    foreach ($__pug_attributes as $__pug_argument) {',
            '        $__pug_name = ltrim($__pug_argument[1], "$");',
            '        $__pug_names[] = $__pug_name;',
            '        ${$__pug_name} = null;',
            '    }',
            '    foreach ($__pug_attributes as $__pug_argument) {',
            '        $__pug_name = ltrim($__pug_argument[1], "$");',
            '        $__pug_names[] = $__pug_name;',
            '        if ($__pug_argument[0]) {',
            '            ${$__pug_name} = $__pug_values;',
            '            break;',
            '        }',
            '        ${$__pug_name} = array_shift($__pug_values);',
            '        if (is_null(${$__pug_name}) && isset($__pug_argument[2])) {',
            '            ${$__pug_name} = $__pug_argument[2];',
            '        }',
            '    }',
            '    foreach ($__pug_mixin_vars as $__pug_key => &$__pug_value) {',
            '        if (!in_array($__pug_key, $__pug_names)) {',
            '            $$__pug_key = &$__pug_value;',
            '        }',
            '    }',
            '    '.$children,
            '};',
        ]));

        $mixins = $this->formatter->getMixins();

        if (!$mixins->has($id)) {
            $this->formatter->getMixins()->register($id, $mixinCode);

            return '';
        }

        if ($mixin->hasParent()) {
            $saveVariable = '$__pug_save_'.mt_rand(0, 9999999);
            $mixinCode = $this->handleCode($saveVariable.'='.$variable).$mixinCode;
            $parent = $mixin->getParent();
            $destructors = $this->formatter->getDestructors();
            $parentDestructors = $destructors->offsetExists($parent)
                ? $destructors->offsetGet($parent)
                : [];
            $parentDestructors[] = new CodeElement($variable.'='.$saveVariable);
            $destructors->offsetSet($parent, $parentDestructors);
        }

        return $mixinCode;
    }

    protected function formatMixinCallElement(MixinCallElement $mixinCall)
    {
        $hasBlock = $mixinCall->hasChildren();
        $children = new PhpUnwrap($this->formatElementChildren($mixinCall), $this->formatter);
        $mixinName = $mixinCall->getName();
        $name = is_string($mixinName)
            ? var_export($mixinName, true)
            : $this->formatter->formatCode($mixinName->getValue());
        is_string($mixinName)
            ? $this->formatter->requireMixin($mixinName)
            : $this->formatter->requireAllMixins();
        $arguments = [];
        $attributes = [];
        foreach ($mixinCall->getAttributes() as $attribute) {
            /* @var AttributeElement $attribute */
            if (is_null($attribute->getName())) {
                $value = $this->formatMixinAttributeValue($attribute->getValue());
                $arguments[] = '['.
                    ($attribute->isVariadic() ? 'true' : 'false').', '.
                    $this->formatCode($value, true).
                    ']';

                continue;
            }

            array_push($attributes, $attribute);
        }
        $attributesExpression = count($attributes)
            ? $this->formatter->formatAttributesList($attributes)
            : new ExpressionElement('[]', $mixinCall->getOriginNode());
        $attributesExpression->preventFromTransformation();
        $mergeAttributes = [];
        foreach ($mixinCall->getAssignments() as $assignment) {
            if ($assignment->getName() === 'attributes') {
                foreach ($assignment->getAttributes() as $attribute) {
                    /* @var AttributeElement $attribute */
                    $value = $this->formatMixinAttributeValue($attribute->getValue());
                    $mergeAttributes[] = $this->formatter->formatCode($value);
                }
            }
        }
        if (count($mergeAttributes)) {
            $attributesExpression->setValue(sprintf(
                'array_merge(%s, %s)',
                $attributesExpression->getValue(),
                implode(', ', $mergeAttributes)
            ));
        }
        $variable = '$__pug_mixins[$__pug_mixin_name]';
        $debug = $this->getOption('debug');

        return $this->handleCode(implode("\n", [
            'if (!isset($__pug_mixins)) {',
            '    $__pug_mixins = [];',
            '}',
            '$__pug_mixin_vars = [];',
            'foreach (array_keys(get_defined_vars()) as $key) {',
            '    if (mb_substr($key, 0, 6) === \'__pug_\' || in_array($key, [\'attributes\', \'block\'])) {',
            '        continue;',
            '    }',
            '    $ref = &$GLOBALS[$key];',
            '    $value = &$$key;',
            '    if($ref !== $value){',
            '        $__pug_mixin_vars[$key] = &$value;',

            '        continue;',
            '    }',
            '    $savedValue = $value;',
            '    $value = ($value === true) ? false : true;',
            '    $isGlobalReference = ($value === $ref);',
            '    $value = $savedValue;',

            '    if (!$isGlobalReference) {',
            '        $__pug_mixin_vars[$key] = &$value;',
            '    }',
            '}',
            'if (!isset($__pug_children)) {',
            '    $__pug_children = null;',
            '}',
            '$__pug_mixin_name = '.$name.';',
            $debug
                ? 'if (!isset('.$variable.')) {'."\n".
                '    throw new \InvalidArgumentException('.
                        '"Unknown $__pug_mixin_name mixin called."'.
                    ');'."\n".
                '}'."\n"
                : 'isset('.$variable.') && ',
            $variable.'('.var_export($hasBlock, true).', '.implode(', ', [
                // $attributes
                $this->formatCode($attributesExpression->getValue(), true),
                // $__pug_arguments
                '['.implode(', ', $arguments).']',
                // $__pug_mixin_vars
                '$__pug_mixin_vars',
                // $__pug_children
                'function ($__pug_children_vars) use ('.
                    '&$__pug_mixins, '.
                    '$__pug_children, '.
                    '&$'.$this->getOption('dependencies_storage').
                ') {'."\n".
                '    foreach (array_keys($__pug_children_vars) as $key) {'."\n".
                '        if (mb_substr($key, 0, 6) === \'__pug_\') {'."\n".
                '            continue;'."\n".
                '        }'."\n".
                '        $ref = &$GLOBALS[$key];'."\n".
                '        $value = &$__pug_children_vars[$key];'."\n".
                '        if($ref !== $value){'."\n".
                '            $$key = &$value;'."\n".
                '            continue;'."\n".
                '        }'."\n".
                '    }'."\n".
                '    '.$children."\n".
                '}',
            ]).');',
        ]));
    }

    protected function getChildrenIterator(ElementInterface $element)
    {
        foreach ($element->getChildren() as $child) {
            yield $child;
        }

        $destructors = $this->formatter->getDestructors();

        if ($destructors->offsetExists($element)) {
            foreach ($destructors->offsetGet($element) as $child) {
                yield $child;
            }
        }
    }

    protected function formatElementChildren(ElementInterface $element, $indentStep = 1)
    {
        $indentLevel = $this->formatter->getLevel();
        $this->formatter->setLevel($indentLevel + $indentStep);
        $content = '';
        $previous = null;
        $commentPattern = $this->getOption('debug') ? $this->debugCommentPattern : null;
        foreach ($this->getChildrenIterator($element) as $child) {
            if (!($child instanceof ElementInterface)) {
                continue;
            }

            $childContent = $this->formatter->format($child);

            if ($child instanceof CodeElement &&
                $previous instanceof CodeElement &&
                $previous->isCodeBlock()
            ) {
                $content = mb_substr($content, 0, -2);
                $childContent = preg_replace('/^<\\?(?:php)?\\s/', '', $childContent);
                if ($commentPattern &&
                    ($pos = mb_strpos($childContent, $commentPattern)) !== false && (
                        ($end = mb_strpos($childContent, '?>')) === false ||
                        $pos < $end
                    ) &&
                    preg_match('/\\}\\s*$/', $content)
                ) {
                    $content = preg_replace(
                        '/\\}\\s*$/',
                        preg_replace('/\\?><\\?php(?:php)?(\s+\\?><\\?php(?:php)?)*/', '\\\\0', $childContent, 1),
                        $content
                    );
                    $childContent = '';
                }
            }

            $content .= $childContent;
            $previous = $child;
        }
        $this->formatter->setLevel($indentLevel);

        return $content;
    }

    protected function formatDocumentElement(DocumentElement $document)
    {
        return $this->formatElementChildren($document, 0);
    }

    protected function throwException($message, ElementInterface $element = null)
    {
        $location = ($node = $element->getOriginNode()) && ($loc = $node->getSourceLocation())
            ? clone $loc
            : new SourceLocation(null, 0, 0);

        throw new FormatterException($location, $message);
    }
}

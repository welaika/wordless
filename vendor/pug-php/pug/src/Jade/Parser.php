<?php

namespace Jade;

use Jade\Parser\Exception as ParserException;
use Jade\Parser\ExtensionsHelper;

class Parser
{
    public static $includeNotFound = '.alert.alert-danger Page not found.';

    protected $allowMixedIndent;
    protected $basedir;
    protected $extending;
    protected $extension;
    protected $filename;
    protected $input;
    protected $lexer;
    protected $notFound;
    protected $options = array();
    protected $preRender;

    protected $blocks = array();
    protected $mixins = array();
    protected $contexts = array();

    public function __construct($input, $filename = null, array $options = array())
    {
        $defaultOptions = array(
            'allowMixedIndent' => true,
            'basedir' => null,
            'customKeywords' => array(),
            'extension' => array('.pug', '.jade'),
            'notFound' => null,
            'preRender' => null,
        );
        foreach ($defaultOptions as $key => $default) {
            $this->$key = isset($options[$key]) ? $options[$key] : $default;
            $this->options[$key] = $this->$key;
        }

        $this->setInput($filename, $input);

        if ($this->input && $this->input[0] === "\xef" && $this->input[1] === "\xbb" && $this->input[2] === "\xbf") {
            $this->input = substr($this->input, 3);
        }

        $this->lexer = new Lexer($this->input, $this->options);
        array_push($this->contexts, $this);
    }

    protected function getExtensions()
    {
        $extensions = new ExtensionsHelper($this->extension);

        return $extensions->getExtensions();
    }

    protected function hasValidTemplateExtension($path)
    {
        $extensions = new ExtensionsHelper($this->extension);

        return $extensions->hasValidTemplateExtension($path);
    }

    protected function getTemplatePath($path)
    {
        $isAbsolutePath = substr($path, 0, 1) === '/';
        if ($isAbsolutePath && !isset($this->options['basedir'])) {
            throw new \ErrorException("The 'basedir' option need to be set to use absolute path like $path", 29);
        }

        $path = ($isAbsolutePath
            ? rtrim($this->options['basedir'], '/\\')
            : dirname($this->filename)
        ) . DIRECTORY_SEPARATOR . $path;
        $extensions = new ExtensionsHelper($this->extension);

        return $extensions->findValidTemplatePath($path, '');
    }

    protected function getTemplateContents($path, $value = null)
    {
        if ($path !== null) {
            $contents = file_get_contents($path);
            if (is_callable($this->preRender)) {
                $contents = call_user_func($this->preRender, $contents);
            }

            return $contents;
        }

        $notFound = isset($this->options['notFound'])
            ? $this->options['notFound']
            : static::$includeNotFound;

        if ($notFound !== false) {
            return $notFound;
        }

        $value = $value ?: $path;
        throw new \InvalidArgumentException("The included file '$value' does not exists.", 22);
    }

    protected function setInput($filename, $input)
    {
        if ($filename === null && file_exists($input)) {
            $filename = $input;
            $input = file_get_contents($input);
        }

        $this->input = preg_replace('`\r\n|\r`', "\n", $input);
        if (is_callable($this->preRender)) {
            $this->input = call_user_func($this->preRender, $this->input);
        }
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get a parser with the same settings.
     *
     * @return Parser
     */
    public function subParser($input)
    {
        return new static($input, $this->filename, $this->options);
    }

    public function context($parser = null)
    {
        if ($parser === null) {
            return array_pop($this->contexts);
        }
        array_push($this->contexts, $parser);
    }

    public function advance()
    {
        return $this->lexer->advance();
    }

    public function skip($n)
    {
        while ($n--) {
            $this->advance();
        }
    }

    public function peek()
    {
        return $this->lookahead(1);
    }

    public function line()
    {
        return $this->lexer->lineno;
    }

    public function lookahead($n = 1)
    {
        return $this->lexer->lookahead($n);
    }

    public function parse()
    {
        $block = new Nodes\Block();
        $block->line = $this->line();

        while ($this->peekType() !== 'eos') {
            if ($this->peekType() === 'newline') {
                $this->advance();
                continue;
            }
            $block->push($this->parseExpression());
        }

        if ($parser = $this->extending) {
            $this->context($parser);
            // $parser->blocks = $this->blocks;
            try {
                $ast = $parser->parse();
            } catch (\Exception $e) {
                throw new ParserException($parser->getFilename() . ' (' . $block->line . ') : ' . $e->getMessage(), 23, $e);
            }
            $this->context();

            foreach ($this->mixins as $name => $v) {
                $ast->unshift($this->mixins[$name]);
            }

            return $ast;
        }

        return $block;
    }

    protected function expect($type)
    {
        if ($this->peekType() === $type) {
            return $this->lexer->advance();
        }

        $lineNumber = $this->line();
        $lines = explode("\n", $this->input);
        $lineString = isset($lines[$lineNumber]) ? $lines[$lineNumber] : '';
        throw new \ErrorException("\n" . sprintf('Expected %s, but got %s in %dth line : %s', $type, $this->peekType(), $lineNumber, $lineString) . "\n", 24);
    }

    protected function accept($type)
    {
        if ($this->peekType() === $type) {
            return $this->advance();
        }
    }

    protected function parseExpression()
    {
        $_types = array('tag', 'mixin', 'block', 'case', 'when', 'default', 'extends', 'include', 'doctype', 'filter', 'comment', 'text', 'each', 'customKeyword', 'code', 'call', 'interpolation');

        if (in_array($this->peekType(), $_types)) {
            $_method = 'parse' . ucfirst($this->peekType());

            return $this->$_method();
        }

        switch ($this->peekType()) {
            case 'yield':
                $this->advance();
                $block = new Nodes\Block();
                $block->yield = true;

                return $block;

            case 'id':
            case 'class':
                $token = $this->advance();
                $this->lexer->defer($this->lexer->token('tag', 'div'));
                $this->lexer->defer($token);

                return $this->parseExpression();

            default:
                throw new \ErrorException($this->filename . ' (' . $this->line() . ') : Unexpected token "' . $this->peekType() . '"', 25);
        }
    }

    protected function parseText()
    {
        $token = $this->expect('text');
        if (preg_match('/^(.*?)#\[([^\]\n]+)\]/', $token->value)) {
            $block = new Nodes\Block();
            $this->parseInlineTags($block, $token->value);

            return $block;
        }
        $node = new Nodes\Text($token->value);
        $node->line = $this->line();

        return $node;
    }

    protected function parseBlockExpansion()
    {
        if (':' === $this->peekType()) {
            $this->advance();

            return new Nodes\Block($this->parseExpression());
        }

        return $this->block();
    }

    protected function parseCase()
    {
        $value = $this->expect('case')->value;
        $node = new Nodes\CaseNode($value);
        $node->line = $this->line();
        $node->block = $this->block();

        return $node;
    }

    protected function parseWhen()
    {
        $value = $this->expect('when')->value;

        return new Nodes\When($value, $this->parseBlockExpansion());
    }

    protected function parseDefault()
    {
        $this->expect('default');

        return new Nodes\When('default', $this->parseBlockExpansion());
    }

    protected function parseCode()
    {
        $token = $this->expect('code');
        $buffer = isset($token->buffer) ? $token->buffer : false;
        $escape = isset($token->escape) ? $token->escape : true;
        $node = new Nodes\Code($token->value, $buffer, $escape);
        $node->line = $this->line();

        $i = 1;
        while ($this->lookahead($i)->type === 'newline') {
            $i++;
        }

        if ($this->lookahead($i)->type === 'indent') {
            $this->skip($i - 1);
            $node->block = $this->block();
        }

        return $node;
    }

    protected function parseComment()
    {
        $token = $this->expect('comment');
        $node = new Nodes\Comment($token->value, $token->buffer);
        $node->line = $this->line();

        return $node;
    }

    protected function parseDoctype()
    {
        $token = $this->expect('doctype');
        $node = new Nodes\Doctype($token->value);
        $node->line = $this->line();

        return $node;
    }

    protected function parseFilterContent()
    {
        if ($this->lookahead(1)->type === 'text') {
            return new Nodes\Block(new Nodes\Text($this->expect('text')->value));
        }

        $this->lexer->pipeless = true;
        $block = $this->parseTextBlock();
        $this->lexer->pipeless = false;

        return $block;
    }

    protected function parseFilter()
    {
        $token = $this->expect('filter');
        $attributes = $this->accept('attributes');

        $block = $this->parseFilterContent();

        $node = new Nodes\Filter($token->value, $block, $attributes);
        $node->line = $this->line();

        return $node;
    }

    protected function parseEach()
    {
        $token = $this->expect('each');
        $node = new Nodes\Each($token->code, $token->value, $token->key);
        $node->line = $this->line();
        $node->block = $this->block();
        if ($this->peekType() === 'code' && $this->peek()->value === 'else') {
            $this->advance();
            $node->alternative = $this->block();
        }

        return $node;
    }

    protected function parseCustomKeyword()
    {
        $token = $this->expect('customKeyword');
        $node = new Nodes\CustomKeyword($token->value, $token->args);
        $node->line = $this->line();
        if ('indent' === $this->peekType()) {
            $node->block = $this->block();
        }

        return $node;
    }

    protected function parseExtends()
    {
        $extendValue = $this->expect('extends')->value;
        $path = $this->getTemplatePath($extendValue);

        $string = $this->getTemplateContents($path, $extendValue);
        $parser = new static($string, $path, $this->options);
        // need to be a reference, or be seted after the parse loop
        $parser->blocks = &$this->blocks;
        $parser->contexts = $this->contexts;
        $this->extending = $parser;

        return new Nodes\Literal('');
    }

    protected function parseBlock()
    {
        $block = $this->expect('block');
        $mode = $block->mode;
        $name = trim($block->value);

        $block = 'indent' === $this->peekType()
            ? $this->block()
            : new Nodes\Block(empty($name)
                ? new Nodes\MixinBlock()
                : new Nodes\Literal('')
            );

        if (isset($this->blocks[$name])) {
            $prev = &$this->blocks[$name];

            switch ($prev->mode) {
                case 'append':
                    $block->nodes = array_merge($block->nodes, $prev->nodes);
                    $prev = $block;
                    break;

                case 'prepend':
                    $block->nodes = array_merge($prev->nodes, $block->nodes);
                    $prev = $block;
                    break;

                case 'replace':
                default:
                    break;
            }

            return $this->blocks[$name];
        }

        $block->mode = $mode;
        $this->blocks[$name] = $block;

        return $block;
    }

    protected function parseInclude()
    {
        $token = $this->expect('include');
        $includeValue = trim($token->value);
        $path = $this->getTemplatePath($includeValue);

        if ($path && !$this->hasValidTemplateExtension($path)) {
            return new Nodes\Text(file_get_contents($path));
        }

        $string = $this->getTemplateContents($path, $includeValue);

        $parser = new static($string, $path, $this->options);
        $parser->blocks = $this->blocks;
        $parser->mixins = $this->mixins;

        $this->context($parser);
        try {
            $ast = $parser->parse();
        } catch (\Exception $e) {
            throw new \ErrorException($path . ' (' . $parser->lexer->lineno . ') : ' . $e->getMessage(), 27);
        }
        $this->context();
        $ast->filename = $path;

        if ('indent' === $this->peekType() && method_exists($ast, 'includeBlock')) {
            $block = $ast->includeBlock();
            if (is_object($block)) {
                $handler = count($block->nodes) === 1 && isset($block->nodes[0]->block)
                    ? $block->nodes[0]->block
                    : $block;
                $handler->push($this->block());
            }
        }

        return $ast;
    }

    protected function parseCall()
    {
        $token = $this->expect('call');
        $name = $token->value;

        $arguments = isset($token->arguments)
            ? $token->arguments
            : null;

        $mixin = new Nodes\Mixin($name, $arguments, new Nodes\Block(), true);

        $this->tag($mixin);

        if ($mixin->block->isEmpty()) {
            $mixin->block = null;
        }

        return $mixin;
    }

    protected function parseMixin()
    {
        $token = $this->expect('mixin');
        $name = $token->value;
        $arguments = $token->arguments;

        // definition
        if ('indent' === $this->peekType()) {
            $mixin = new Nodes\Mixin($name, $arguments, $this->block(), false);
            $this->mixins[$name] = $mixin;

            return $mixin;
        }

        // call
        return new Nodes\Mixin($name, $arguments, null, true);
    }

    protected function parseTextBlock()
    {
        $block = new Nodes\Block();
        $block->line = $this->line();
        $spaces = $this->expect('indent')->value;

        if (!isset($this->_spaces)) {
            $this->_spaces = $spaces;
        }

        $indent = str_repeat(' ', $spaces - $this->_spaces + 1);

        while ($this->peekType() != 'outdent') {
            switch ($this->peekType()) {
                case 'newline':
                    $this->lexer->advance();
                    break;

                case 'indent':
                    foreach ($this->parseTextBlock()->nodes as $n) {
                        $block->push($n);
                    }
                    break;

                default:
                    $this->parseInlineTags($block, $indent . $this->advance()->value);
            }
        }

        if (isset($this->_spaces) && $spaces === $this->_spaces) {
            unset($this->_spaces);
        }

        $this->expect('outdent');

        return $block;
    }

    protected function block()
    {
        $block = new Nodes\Block();
        $block->line = $this->line();
        $this->expect('indent');

        while ($this->peekType() !== 'outdent') {
            if ($this->peekType() === 'newline') {
                $this->lexer->advance();
                continue;
            }

            $block->push($this->parseExpression());
        }

        $this->expect('outdent');

        return $block;
    }

    protected function parseInterpolation()
    {
        $token = $this->advance();
        $tag = new Nodes\Tag($token->value);
        $tag->buffer = true;

        return $this->tag($tag);
    }

    protected function parseASTFilter()
    {
        $token = $this->expect('tag');
        $attributes = $this->accept('attributes');
        $this->expect(':');
        $block = $this->block();
        $node = new Nodes\Filter($token->value, $block, $attributes);
        $node->line = $this->line();

        return $node;
    }

    protected function parseTag()
    {
        $i = 2;

        if ('attributes' === $this->lookahead($i)->type) {
            $i++;
        }

        if (':' === $this->lookahead($i)->type) {
            $i++;

            if ('indent' === $this->lookahead($i)->type) {
                return $this->parseASTFilter();
            }
        }

        $token = $this->advance();
        $tag = new Nodes\Tag($token->value);

        $tag->selfClosing = isset($token->selfClosing)
            ? $token->selfClosing
            : false;

        return $this->tag($tag);
    }

    public function parseInlineTags($block, $str)
    {
        while (preg_match('/^(.*?)#\[([^\]\n]+)\]/', $str, $matches)) {
            if (!empty($matches[1])) {
                $text = new Nodes\Text($matches[1]);
                $text->line = $this->line();
                $block->push($text);
            }
            $parser = $this->subParser($matches[2]);
            $tag = $parser->parse();
            $tag->line = $this->line();
            $block->push($tag);
            $str = substr($str, strlen($matches[0]));
        }
        if (substr($str, 0, 1) === ' ') {
            $str = substr($str, 1);
        }
        $text = new Nodes\Text($str);
        $text->line = $this->line();
        $block->push($text);
    }

    protected function peekType()
    {
        return ($peek = $this->peek())
            ? $peek->type
            : null;
    }

    protected function tag($tag)
    {
        $tag->line = $this->line();

        while (true) {
            switch ($type = $this->peekType()) {
                case 'id':
                    $token = $this->advance();
                    $peek = $this->peek();
                    $escaped = isset($peek->escaped, $peek->escaped[$type]) && $peek->escaped[$type];
                    $value = $escaped || !isset($peek->attributes, $peek->attributes[$type])
                        ? "'" . $token->value . "'"
                        : $peek->attributes[$type];
                    $tag->setAttribute($token->type, $value, $escaped);
                    unset($peek->attributes[$type]);
                    continue;

                case 'class':
                    $token = $this->advance();
                    $tag->setAttribute($token->type, "'" . $token->value . "'");
                    continue;

                case 'attributes':
                    $token = $this->advance();
                    $obj = $token->attributes;
                    $escaped = $token->escaped;
                    $nameList = array_keys($obj);

                    if ($token->selfClosing) {
                        $tag->selfClosing = true;
                    }

                    foreach ($nameList as $name) {
                        $value = $obj[$name];
                        $normalizedValue = strtolower($value);
                        if ($normalizedValue === 'true' || $normalizedValue === 'false') {
                            $value = $normalizedValue === 'true';
                        }
                        $tag->setAttribute($name, $value, $escaped[$name]);
                    }
                    continue;

                case '&attributes':
                    $token = $this->advance();
                    $tag->setAttribute('&attributes', $token->value);
                    continue;

                default:
                    break 2;
            }
        }

        $dot = false;
        $tag->textOnly = false;
        if ('.' === $this->peek()->value) {
            $dot = $tag->textOnly = true;
            $this->advance();
        }

        switch ($this->peekType()) {
            case 'text':
                $this->parseInlineTags($tag->block, $this->expect('text')->value);
                break;

            case 'code':
                $tag->code = $this->parseCode();
                break;

            case ':':
                $this->advance();
                $tag->block = new Nodes\Block();
                $tag->block->push($this->parseExpression());
                break;
        }

        while ('newline' === $this->peekType()) {
            $this->advance();
        }

        if ('script' === $tag->name) {
            $type = $tag->getAttribute('type');

            if ($type !== null) {
                $type = preg_replace('/^[\'\"]|[\'\"]$/', '', $type);

                if (!$dot && 'text/javascript' != $type['value']) {
                    $tag->textOnly = false;
                }
            }
        }

        if ('indent' === $this->peekType()) {
            if ($tag->textOnly) {
                $this->lexer->pipeless = true;
                $tag->block = $this->parseTextBlock();
                $this->lexer->pipeless = false;

                return $tag;
            }

            $block = $this->block();

            if ($tag->block && !$tag->block->isEmpty()) {
                foreach ($block->nodes as $n) {
                    $tag->block->push($n);
                }

                return $tag;
            }

            $tag->block = $block;
        }

        return $tag;
    }
}

<?php

namespace Phug;

use Phug\Lexer\Token\AssignmentToken;
use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\AttributeToken;
use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\Token\CaseToken;
use Phug\Lexer\Token\ClassToken;
use Phug\Lexer\Token\CodeToken;
use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\Token\ConditionalToken;
use Phug\Lexer\Token\DoctypeToken;
use Phug\Lexer\Token\DoToken;
use Phug\Lexer\Token\EachToken;
use Phug\Lexer\Token\ExpansionToken;
use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\FilterToken;
use Phug\Lexer\Token\ForToken;
use Phug\Lexer\Token\IdToken;
use Phug\Lexer\Token\ImportToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\Token\KeywordToken;
use Phug\Lexer\Token\MixinCallToken;
use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\Token\TagToken;
use Phug\Lexer\Token\TextToken;
use Phug\Lexer\Token\VariableToken;
use Phug\Lexer\Token\WhenToken;
use Phug\Lexer\Token\WhileToken;
use Phug\Lexer\Token\YieldToken;
use Phug\Parser\Event\NodeEvent;
use Phug\Parser\Event\ParseEvent;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\NodeInterface;
use Phug\Parser\State;
use Phug\Parser\TokenHandler\AssignmentTokenHandler;
use Phug\Parser\TokenHandler\AttributeEndTokenHandler;
use Phug\Parser\TokenHandler\AttributeStartTokenHandler;
use Phug\Parser\TokenHandler\AttributeTokenHandler;
use Phug\Parser\TokenHandler\AutoCloseTokenHandler;
use Phug\Parser\TokenHandler\BlockTokenHandler;
use Phug\Parser\TokenHandler\CaseTokenHandler;
use Phug\Parser\TokenHandler\ClassTokenHandler;
use Phug\Parser\TokenHandler\CodeTokenHandler;
use Phug\Parser\TokenHandler\CommentTokenHandler;
use Phug\Parser\TokenHandler\ConditionalTokenHandler;
use Phug\Parser\TokenHandler\DoctypeTokenHandler;
use Phug\Parser\TokenHandler\DoTokenHandler;
use Phug\Parser\TokenHandler\EachTokenHandler;
use Phug\Parser\TokenHandler\ExpansionTokenHandler;
use Phug\Parser\TokenHandler\ExpressionTokenHandler;
use Phug\Parser\TokenHandler\FilterTokenHandler;
use Phug\Parser\TokenHandler\ForTokenHandler;
use Phug\Parser\TokenHandler\IdTokenHandler;
use Phug\Parser\TokenHandler\ImportTokenHandler;
use Phug\Parser\TokenHandler\IndentTokenHandler;
use Phug\Parser\TokenHandler\InterpolationEndTokenHandler;
use Phug\Parser\TokenHandler\InterpolationStartTokenHandler;
use Phug\Parser\TokenHandler\KeywordTokenHandler;
use Phug\Parser\TokenHandler\MixinCallTokenHandler;
use Phug\Parser\TokenHandler\MixinTokenHandler;
use Phug\Parser\TokenHandler\NewLineTokenHandler;
use Phug\Parser\TokenHandler\OutdentTokenHandler;
use Phug\Parser\TokenHandler\TagInterpolationEndTokenHandler;
use Phug\Parser\TokenHandler\TagInterpolationStartTokenHandler;
use Phug\Parser\TokenHandler\TagTokenHandler;
use Phug\Parser\TokenHandler\TextTokenHandler;
use Phug\Parser\TokenHandler\VariableTokenHandler;
use Phug\Parser\TokenHandler\WhenTokenHandler;
use Phug\Parser\TokenHandler\WhileTokenHandler;
use Phug\Parser\TokenHandler\YieldTokenHandler;
use Phug\Parser\TokenHandlerInterface;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\ModuleContainerTrait;

/**
 * Takes tokens from the Lexer and creates an AST out of it.
 *
 * This class takes generated tokens from the Lexer sequentially
 * and produces an Abstract Syntax Tree (AST) out of it
 *
 * The AST is an object-tree containing Phug\Parser\Node instances
 * with parent/child relations
 *
 * This AST is passed to the compiler to generate PHTML out of it
 *
 * Usage example:
 * <code>
 *
 *     use Phug\Parser;
 *
 *     $parser = new Parser();
 *     var_dump($parser->parse($pugInput));
 *
 * </code>
 */
class Parser implements ModuleContainerInterface
{
    use ModuleContainerTrait;

    /**
     * The lexer used in this parser instance.
     *
     * @var Lexer
     */
    private $lexer;

    /**
     * @var State
     */
    private $state;

    /**
     * @var callable[]
     */
    private $tokenHandlers;

    /**
     * Creates a new parser instance.
     *
     * The parser will run the provided input through the lexer
     * and generate an AST out of it.
     *
     * The AST will be an object-tree consisting of Phug\Parser\Node instances
     *
     * You can take the AST and either compile it with the Compiler or handle it yourself
     *
     * @param array|null $options the options array
     *
     * @throws ParserException
     */
    public function __construct($options = null)
    {
        $this->setOptionsDefaults($options ?: [], [
            'lexer_class_name'        => Lexer::class,
            'parser_state_class_name' => State::class,
            'parser_modules'          => [],
            'keywords'                => [],
            'detailed_dump'           => false,
            'token_handlers'          => [
                AssignmentToken::class            => AssignmentTokenHandler::class,
                AttributeEndToken::class          => AttributeEndTokenHandler::class,
                AttributeStartToken::class        => AttributeStartTokenHandler::class,
                AttributeToken::class             => AttributeTokenHandler::class,
                AutoCloseToken::class             => AutoCloseTokenHandler::class,
                BlockToken::class                 => BlockTokenHandler::class,
                YieldToken::class                 => YieldTokenHandler::class,
                CaseToken::class                  => CaseTokenHandler::class,
                ClassToken::class                 => ClassTokenHandler::class,
                CodeToken::class                  => CodeTokenHandler::class,
                CommentToken::class               => CommentTokenHandler::class,
                ConditionalToken::class           => ConditionalTokenHandler::class,
                DoToken::class                    => DoTokenHandler::class,
                DoctypeToken::class               => DoctypeTokenHandler::class,
                EachToken::class                  => EachTokenHandler::class,
                ExpansionToken::class             => ExpansionTokenHandler::class,
                ExpressionToken::class            => ExpressionTokenHandler::class,
                FilterToken::class                => FilterTokenHandler::class,
                ForToken::class                   => ForTokenHandler::class,
                IdToken::class                    => IdTokenHandler::class,
                InterpolationStartToken::class    => InterpolationStartTokenHandler::class,
                InterpolationEndToken::class      => InterpolationEndTokenHandler::class,
                ImportToken::class                => ImportTokenHandler::class,
                IndentToken::class                => IndentTokenHandler::class,
                MixinCallToken::class             => MixinCallTokenHandler::class,
                MixinToken::class                 => MixinTokenHandler::class,
                NewLineToken::class               => NewLineTokenHandler::class,
                OutdentToken::class               => OutdentTokenHandler::class,
                TagInterpolationStartToken::class => TagInterpolationStartTokenHandler::class,
                TagInterpolationEndToken::class   => TagInterpolationEndTokenHandler::class,
                KeywordToken::class               => KeywordTokenHandler::class,
                TagToken::class                   => TagTokenHandler::class,
                TextToken::class                  => TextTokenHandler::class,
                VariableToken::class              => VariableTokenHandler::class,
                WhenToken::class                  => WhenTokenHandler::class,
                WhileToken::class                 => WhileTokenHandler::class,
            ],

            //Events
            'on_parse'       => null,
            'on_document'    => null,
            'on_state_enter' => null,
            'on_state_leave' => null,
            'on_state_store' => null,
        ]);

        $lexerClassName = $this->getOption('lexer_class_name');
        if (!is_a($lexerClassName, Lexer::class, true)) {
            throw new \InvalidArgumentException(
                "Passed lexer class $lexerClassName is ".
                'not a valid '.Lexer::class
            );
        }

        $this->lexer = new $lexerClassName($this->getOptions());
        $this->state = null;
        $this->tokenHandlers = [];

        foreach ($this->getOption('token_handlers') as $className => $handler) {
            $this->setTokenHandler($className, $handler);
        }

        if ($onParse = $this->getOption('on_parse')) {
            $this->attach(ParserEvent::PARSE, $onParse);
        }

        if ($onDocument = $this->getOption('on_document')) {
            $this->attach(ParserEvent::DOCUMENT, $onDocument);
        }

        if ($onStateEnter = $this->getOption('on_state_enter')) {
            $this->attach(ParserEvent::STATE_ENTER, $onStateEnter);
        }

        if ($onStateLeave = $this->getOption('on_state_leave')) {
            $this->attach(ParserEvent::STATE_LEAVE, $onStateLeave);
        }

        if ($onStateStore = $this->getOption('on_state_store')) {
            $this->attach(ParserEvent::STATE_STORE, $onStateStore);
        }

        $this->addModules($this->getOption('parser_modules'));
    }

    /**
     * Returns the currently used Lexer instance.
     *
     * @return Lexer
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    public function setTokenHandler($className, $handler)
    {
        if (!is_subclass_of($handler, TokenHandlerInterface::class)) {
            throw new \InvalidArgumentException(
                'Passed token handler needs to implement '.TokenHandlerInterface::class
            );
        }

        $this->tokenHandlers[$className] = $handler;

        return $this;
    }

    /**
     * Parses the provided input-string to an AST.
     *
     * The Abstract Syntax Tree (AST) will be an object-tree consisting
     * of \Phug\Parser\Node instances.
     *
     * You can either let the compiler compile it or compile it yourself
     *
     * The root-node will always be of type 'document',
     * from there on it can contain several kinds of nodes
     *
     * @param string $input the input jade string that is to be parsed
     * @param string $path  optional path of file the input comes from
     *
     * @return NodeInterface the root-node of the parsed AST
     */
    public function parse($input, $path = null)
    {
        $stateClassName = $this->getOption('parser_state_class_name');

        $event = new ParseEvent($input, $path, $stateClassName, [
            'token_handlers' => $this->tokenHandlers,
        ]);

        $this->trigger($event);

        $input = $event->getInput();
        $path = $event->getPath();
        $stateClassName = $event->getStateClassName();
        $stateOptions = $event->getStateOptions();

        $stateOptions['path'] = $path;

        if (!is_a($stateClassName, State::class, true)) {
            throw new \InvalidArgumentException(
                'parser_state_class_name needs to be a valid '.State::class.' sub class'
            );
        }

        $this->state = new $stateClassName(
            $this,
            //Append a new line to get last outdents and tag if not ending with \n
            $this->lexer->lex($input."\n", $path),
            $stateOptions
        );

        $forward = function (NodeEvent $event) {
            return $this->trigger($event);
        };

        //Forward events from the state
        $this->state->attach(ParserEvent::STATE_ENTER, $forward);
        $this->state->attach(ParserEvent::STATE_LEAVE, $forward);
        $this->state->attach(ParserEvent::STATE_STORE, $forward);

        //While we have tokens, handle current token, then go to next token
        //rinse and repeat
        while ($this->state->hasTokens()) {
            $this->state->handleToken();
            $this->state->nextToken();
        }
        $this->state->store();

        $document = $this->state->getDocumentNode();

        //Some work after parsing needed
        //Resolve expansions/outer nodes
        /** @var NodeInterface[] $expandingNodes */
        $expandingNodes = $document->find(function (NodeInterface $node) {
            return $node->getOuterNode() !== null;
        });

        foreach ($expandingNodes as $expandingNode) {
            $current = $expandingNode;
            while ($outerNode = $expandingNode->getOuterNode()) {

                /** @var NodeInterface $expandedNode */
                $expandedNode = $outerNode;
                $current->setOuterNode(null);
                $current->prepend($expandedNode);
                $current->remove();
                $expandedNode->appendChild($current);
                $current = $expandedNode;
            }
        }

        $this->state->clearListeners(ParserEvent::STATE_ENTER);
        $this->state->clearListeners(ParserEvent::STATE_LEAVE);
        $this->state->clearListeners(ParserEvent::STATE_STORE);
        $this->state = null;

        $event = new NodeEvent(ParserEvent::DOCUMENT, $document);
        $this->trigger($event);

        //Return the final document node with all its awesome child nodes
        return $event->getNode();
    }

    /**
     * Dump a representation of the parser rendering.
     *
     * @param string $input the input jade string that is to be parsed
     *
     * @return string representation of parser rendering
     */
    public function dump($input)
    {
        return $this->dumpNode($this->parse($input));
    }

    protected function getNodeName(NodeInterface $node)
    {
        switch (get_class($node)) {
            case ElementNode::class:
                $text = get_class($node);
                if (!$this->getOption('detailed_dump')) {
                    if ($outerNode = $node->getOuterNode()) {
                        $text .= ' outer='.$this->getNodeName($outerNode);
                    }

                    return $text;
                }

                $text .= ':'.$node->getName() ?: 'div';
                $ids = '';
                $classes = '';
                $attributes = [];
                foreach ($node->getAttributes() as $attribute) {
                    if ($attribute->getName() === 'id') {
                        $ids .= '#'.$attribute->getValue();

                        continue;
                    }
                    if ($attribute->getName() === 'class') {
                        $classes .= '.'.$attribute->getValue();

                        continue;
                    }
                    $attributes[] = $attribute->getName().'='.$attribute->getValue();
                }
                $attributes = count($attributes) ? '('.implode(' ', $attributes).')' : '';
                $text .= $ids.$classes.$attributes;

                if ($outerNode = $node->getOuterNode()) {
                    $text .= ' outer='.$this->getNodeName($outerNode);
                }

                return $text;
            default:
                $text = get_class($node);

                if ($outerNode = $node->getOuterNode()) {
                    $text .= ' outer='.$this->getNodeName($outerNode);
                }

                return $text;
        }
    }

    protected function dumpNode(NodeInterface $node, $level = null)
    {
        $level = $level ?: 0;
        $text = $this->getNodeName($node);

        $text = str_repeat('  ', $level)."[$text]";
        if (count($node) > 0) {
            foreach ($node as $child) {
                $text .= "\n".$this->dumpNode($child, $level + 1);
            }
        }

        return $text;
    }

    public function getModuleBaseClassName()
    {
        return ParserModuleInterface::class;
    }
}

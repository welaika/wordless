<?php

namespace Phug\Parser;

use Phug\EventManagerInterface;
use Phug\EventManagerTrait;
use Phug\Lexer\TokenInterface;
use Phug\Parser;
use Phug\Parser\Event\NodeEvent;
use Phug\Parser\Node\DocumentNode;
use Phug\ParserEvent;
use Phug\ParserException;
use Phug\Util\OptionInterface;
use Phug\Util\Partial\OptionTrait;
use Phug\Util\SourceLocation;
use SplObjectStorage;

class State implements OptionInterface, EventManagerInterface
{
    use OptionTrait;
    use EventManagerTrait;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var int
     */
    private $level;

    /**
     * The Iterator returned by the ->lex() method of the lexer.
     *
     * @var \Iterator
     */
    private $tokens;

    /**
     * The root node of the currently parsed document.
     *
     * @var DocumentNode
     */
    private $documentNode;

    /**
     * The parent that currently found childs are appended to.
     *
     * When an <outdent>-token is encountered, it moves one parent up
     * ($_currentParent->parent becomes the new $_currentParent)
     *
     * @var Node
     */
    private $parentNode;

    /**
     * The current element in the queue.
     *
     * Will be appended to $_currentParent when a <newLine>-token is encountered
     * It will become the current parent, if an <indent>-token is encountered
     *
     * @var Node
     */
    private $currentNode;

    /**
     * The last element that was completely put together.
     *
     * Will be set on a <newLine>-token ($_current will become last)
     *
     * @var Node
     */
    private $lastNode;

    /**
     * Stores an expanded node to attach it to the expanding node later.
     *
     * @var Node
     */
    private $outerNode;

    /**
     * Stores handlers by names.
     *
     * @var array
     */
    private $namedHandlers;

    /**
     * Stack of interpolation to enter/leave.
     *
     * @var SplObjectStorage
     */
    private $interpolationStack;

    /**
     * Stack of interpolation base nodes.
     *
     * @var array
     */
    private $interpolationNodes;

    public function __construct(Parser $parser, \Iterator $tokens, array $options = null)
    {
        $this->parser = $parser;
        $this->level = 0;
        $this->tokens = $tokens;
        $this->documentNode = $this->createNode(DocumentNode::class);
        $this->parentNode = $this->documentNode;
        $this->currentNode = null;
        $this->lastNode = null;
        $this->outerNode = null;
        $this->interpolationNodes = [];
        $this->setOptionsRecursive([
            'token_handlers' => [],
            'path'           => null,
        ], $options ?: []);
    }

    /**
     * @return NodeInterface|null
     */
    public function getInterpolationNode()
    {
        return end($this->interpolationNodes);
    }

    /**
     * @return NodeInterface
     */
    public function popInterpolationNode()
    {
        return array_pop($this->interpolationNodes);
    }

    /**
     * @param NodeInterface $node
     *
     * @return int
     */
    public function pushInterpolationNode(NodeInterface $node)
    {
        return array_push($this->interpolationNodes, $node);
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return \Iterator
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param \Iterator $tokens
     *
     * @return $this
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;

        return $this;
    }

    /**
     * @return DocumentNode
     */
    public function getDocumentNode()
    {
        return $this->documentNode;
    }

    /**
     * @return Node
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * @param Node $currentParent
     *
     * @return $this
     */
    public function setParentNode($currentParent)
    {
        $this->parentNode = $currentParent;

        return $this;
    }

    /**
     * @return Node
     */
    public function getCurrentNode()
    {
        return $this->currentNode;
    }

    /**
     * @param Node $current
     *
     * @return $this
     */
    public function setCurrentNode($current)
    {
        $this->currentNode = $current;

        return $this;
    }

    /**
     * Append to current node, or set as current if node selected.
     *
     * @param Node $node
     *
     * @return $this
     */
    public function append($node)
    {
        $current = $this->getCurrentNode();

        $current
            ? $current->appendChild($node)
            : $this->setCurrentNode($node);

        return $this;
    }

    /**
     * @return Node
     */
    public function getLastNode()
    {
        return $this->lastNode;
    }

    /**
     * @param Node $last
     *
     * @return $this
     */
    public function setLastNode($last)
    {
        $this->lastNode = $last;

        return $this;
    }

    /**
     * @return Node
     */
    public function getOuterNode()
    {
        return $this->outerNode;
    }

    /**
     * @param Node $node
     *
     * @return $this
     */
    public function setOuterNode(Node $node)
    {
        $this->outerNode = $node;

        return $this;
    }

    /**
     * Instanciate a new handler by name or return the previous
     * instancied one with the same name.
     *
     * @param string $handler name
     *
     * @return TokenHandlerInterface
     */
    private function getNamedHandler($handler)
    {
        if (!isset($this->namedHandlers[$handler])) {
            $this->namedHandlers[$handler] = new $handler();
        }

        return $this->namedHandlers[$handler];
    }

    /**
     * Handles any kind of token returned by the lexer.
     *
     * The token handler is translated according to the `token_handlers` option
     *
     * If no token is passed, it will take the current token
     * in the lexer's token generator
     *
     * @param TokenInterface $token a token or the current lexer's generator token
     *
     * @throws ParserException when no token handler has been found
     *
     * @return $this
     */
    public function handleToken(TokenInterface $token = null)
    {
        $token = $token ?: $this->getToken();
        $className = get_class($token);
        $tokenHandlers = $this->getOption('token_handlers');

        if (!isset($tokenHandlers[$className])) {
            $this->throwException(
                "Unexpected token `$className`, no token handler registered",
                0,
                $token
            );
        }

        $handler = $tokenHandlers[$className];
        $handler = $handler instanceof TokenHandlerInterface
            ? $handler
            : $this->getNamedHandler($handler);

        $handler->handleToken($token, $this);

        return $this;
    }

    /**
     * Yields tokens as long as the given types match.
     *
     * Yields tokens of the given types until
     * a token is encountered, that is not given
     * in the types-array
     *
     * @param array $types the token types that are allowed
     *
     * @return iterable
     */
    public function lookUp(array $types)
    {
        while ($this->hasTokens()) {
            $token = $this->getToken();

            if (!in_array(get_class($token), $types, true)) {
                break;
            }

            yield $token;

            $this->nextToken();
        }
    }

    /**
     * Moves on the token generator by one and does ->lookUp().
     *
     * @see Parser->nextToken
     * @see Parser->lookUp
     *
     * @param array $types the types that are allowed
     *
     * @return iterable
     */
    public function lookUpNext(array $types)
    {
        return $this->hasTokens() ? $this->nextToken()->lookUp($types) : [];
    }

    /**
     * Returns the token of the given type if it is in the token queue.
     *
     * If the given token in the queue is not of the given type,
     * this method returns null
     *
     * @param array $types the types that are expected
     *
     * @return Node|null
     */
    public function expect(array $types)
    {
        foreach ($this->lookUp($types) as $token) {
            return $token;
        }
    }

    /**
     * Moves the generator on by one and does ->expect().
     *
     * @see Parser->nextToken
     * @see Parser->expect
     *
     * @param array $types the types that are expected
     *
     * @return Node|null
     */
    public function expectNext(array $types)
    {
        return $this->nextToken()->expect($types);
    }

    /**
     * Returns true, if there are still tokens left to be generated.
     *
     * If the lexer-generator still has tokens to generate,
     * this returns true and false, if it doesn't
     *
     * @see \Generator->valid
     *
     * @return bool
     */
    public function hasTokens()
    {
        return $this->tokens->valid();
    }

    /**
     * Moves the generator on by one token.
     *
     * (It calls ->next() on the generator, look at the PHP doc)
     *
     * @see \Generator->next
     *
     * @return $this
     */
    public function nextToken()
    {
        $this->tokens->next();

        return $this;
    }

    /**
     * Returns the current token in the lexer generator.
     *
     * @see \Generator->current
     *
     * @return TokenInterface|null
     */
    public function getToken()
    {
        return $this->tokens->current();
    }

    /**
     * Return the token parsed just before the current one (->getToken()).
     *
     * @return TokenInterface|null
     */
    public function getPreviousToken()
    {
        return $this->parser->getLexer()->getPreviousToken();
    }

    public function is(Node $node, array $classNames)
    {
        foreach ($classNames as $className) {
            if (is_a($node, $className)) {
                return true;
            }
        }

        return false;
    }

    public function currentNodeIs(array $classNames)
    {
        if (!$this->currentNode) {
            return false;
        }

        return $this->is($this->currentNode, $classNames);
    }

    public function lastNodeIs(array $classNames)
    {
        if (!$this->lastNode) {
            return false;
        }

        return $this->is($this->lastNode, $classNames);
    }

    public function parentNodeIs(array $classNames)
    {
        if (!$this->parentNode) {
            return false;
        }

        return $this->is($this->parentNode, $classNames);
    }

    /**
     * Creates a new node instance with the given type.
     *
     * If a token is given, the location in the code of that token
     * is also passed to the Node instance
     *
     * If no token is passed, a dummy-token with the current
     * lexer's offset and line is created
     *
     * Notice that nodes are expando-objects, you can add properties on-the-fly
     * and retrieve them as an array later
     *
     * @param string         $className the type the node should have
     * @param TokenInterface $token     the token to relate this node to
     *
     * @return Node The newly created node
     */
    public function createNode($className, TokenInterface $token = null)
    {
        if (!is_subclass_of($className, Node::class)) {
            throw new \InvalidArgumentException(
                "$className is not a valid token class"
            );
        }

        return new $className($token, null, $this->level);
    }

    public function enter()
    {
        $this->level++;

        if (!$this->lastNode) {
            return $this;
        }

        $this->parentNode = $this->lastNode;

        $event = new NodeEvent(ParserEvent::STATE_ENTER, $this->lastNode);
        $this->trigger($event);

        return $this;
    }

    public function leave()
    {
        $this->level--;

        if (!$this->parentNode->getParent()) {
            $this->throwException(
                'Failed to outdent: No parent to outdent to. '.
                'Seems the parser moved out too many levels.'
            );
        }

        $node = $this->parentNode;
        $this->parentNode = $this->parentNode->getParent();

        $event = new NodeEvent(ParserEvent::STATE_LEAVE, $node);
        $this->trigger($event);

        return $this;
    }

    /**
     * @return SplObjectStorage
     */
    public function getInterpolationStack()
    {
        if (!$this->interpolationStack) {
            $this->interpolationStack = new SplObjectStorage();
        }

        return $this->interpolationStack;
    }

    public function store()
    {
        if (!$this->currentNode) {
            return $this;
        }

        //Is there any expansion?
        if ($this->outerNode) {
            //Store outer node on current node for expansion
            $this->currentNode->setOuterNode($this->outerNode);
            $this->outerNode = null;
        }

        //Append to current parent
        $this->parentNode->appendChild($this->currentNode);
        $this->lastNode = $this->currentNode;
        $this->currentNode = null;

        $event = new NodeEvent(ParserEvent::STATE_STORE, $this->lastNode);
        $this->trigger($event);

        return $this;
    }

    /**
     * Throws a parser-exception.
     *
     * The current line and offset of the exception
     * get automatically appended to the message
     *
     * @param string         $message      A meaningful error message
     * @param int            $code
     * @param TokenInterface $relatedToken
     * @param null           $previous
     *
     * @throws ParserException
     */
    public function throwException($message, $code = 0, TokenInterface $relatedToken = null, $previous = null)
    {
        $lexer = $this->parser->getLexer();
        //This will basically check for a source location for this Node. The process is like:
        //- If there's a related token, we use the cloned token's source location
        //- If not, we check if we're still lexing right now, so we can assume the node is somewhere
        //  around the current lexing location, we get it from the lexer state directly
        //- If none is found, we create an empty source location
        $location = $relatedToken && $relatedToken->getSourceLocation()
            ? clone $relatedToken->getSourceLocation()
            : (
                $lexer->hasState()
                    ? $lexer->getState()->createCurrentSourceLocation()
                    : new SourceLocation(null, 0, 0)
            );

        throw new ParserException(
            $location,
            ParserException::message($message, [
                'path'   => $location->getPath(),
                'near'   => $lexer->hasState()
                    ? $lexer->getState()->getReader()->peek(20)
                    : '[No clue]',
                'line'   => $location->getLine(),
                'offset' => $location->getOffset(),
            ]),
            $code,
            $relatedToken,
            $previous
        );
    }
}

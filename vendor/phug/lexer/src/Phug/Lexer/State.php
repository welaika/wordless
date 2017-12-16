<?php

namespace Phug\Lexer;

use Phug\Lexer;
use Phug\LexerException;
use Phug\Reader;
use Phug\Util\OptionInterface;
use Phug\Util\Partial\LevelTrait;
use Phug\Util\Partial\OptionTrait;
use Phug\Util\SourceLocation;

/**
 * Represents the state of a currently running lexing process.
 */
class State implements OptionInterface
{
    use OptionTrait;
    use LevelTrait;

    /**
     * Contains the current `Phug\Reader` instance used by the lexer.
     *
     * @var Reader
     */
    private $reader;

    /**
     * Contains the current `Phug\Lexer` instance linked to the state.
     *
     * @var Lexer
     */
    private $lexer;

    /**
     * Contains the currently detected indentation style.
     *
     * @var string
     */
    private $indentStyle;

    /**
     * Contains the currently detected indentation width.
     *
     * @var int
     */
    private $indentWidth;

    /**
     * Contains the stack of indent steps.
     *
     * @var array
     */
    private $indentStack;

    /**
     * Creates a new instance of the state.
     *
     * @param Lexer  $lexer   linked lexer
     * @param string $input   pug string input
     * @param array  $options indent settings, errors info and reader class name
     */
    public function __construct(Lexer $lexer, $input, array $options)
    {
        $this->lexer = $lexer;
        $this->setOptionsRecursive([
            'reader_class_name'  => Reader::class,
            'encoding'           => null,
            'level'              => 0,
            'indent_width'       => null,
            'indent_style'       => null,
            'allow_mixed_indent' => null,
            'path'               => null,
        ], $options ?: []);

        $readerClassName = $this->getOption('reader_class_name');
        if (!is_a($readerClassName, Reader::class, true)) {
            throw new \InvalidArgumentException(
                'Configuration option `reader_class_name` needs to be a valid FQCN of a class that extends '.
                Reader::class
            );
        }

        $this->reader = new $readerClassName(
            $input,
            $this->getOption('encoding')
        );
        $this->indentStyle = $this->getOption('indent_style');
        $this->indentWidth = $this->getOption('indent_width');
        $this->indentStack = [];
        $this->level = $this->getOption('level');

        //This will strip \r, \0 etc. from the input
        $this->reader->normalize();
    }

    /**
     * @return TokenInterface
     */
    public function getLastToken()
    {
        return $this->lexer
            ? $this->lexer->getLastToken()
            : null;
    }

    /**
     * @param $classNames
     *
     * @return bool
     */
    public function lastTokenIs($classNames)
    {
        $token = $this->getLastToken();
        foreach ($classNames as $className) {
            if ($token instanceof $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current lexer instance linked.
     *
     * @return Lexer|null
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * Returns the current reader instance that is used for parsing the input.
     *
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Returns the currently used indentation style.
     *
     * @return string
     */
    public function getIndentStyle()
    {
        return $this->indentStyle;
    }

    /**
     * Returns the currently used indentation style.
     *
     * @return int
     */
    public function getIndentLevel()
    {
        return end($this->indentStack) ?: 0;
    }

    /**
     * Outdent and return the new level.
     *
     * @return int
     */
    public function outdent()
    {
        array_pop($this->indentStack);

        return $this->getIndentLevel();
    }

    /**
     * Return new outdent level if current above expected,
     * or false if expected level reached.
     *
     * @return int
     */
    public function nextOutdent()
    {
        $oldLevel = $this->getIndentLevel();
        $expected = $this->getLevel();

        if ($expected < $oldLevel) {
            $newLevel = $this->outdent();
            if ($newLevel < $expected) {
                $this->throwException(
                    'Inconsistent indentation. '.
                    'Expecting either '.
                    $newLevel.
                    ' or '.
                    $oldLevel.
                    ' spaces/tabs.'
                );
            }

            return $newLevel;
        }

        return false;
    }

    /**
     * Indent and return the new level.
     *
     * @param null $level
     *
     * @return int
     */
    public function indent($level = null)
    {
        $level = $level ?: $this->getLevel();
        array_push($this->indentStack, $level);

        return $level;
    }

    /**
     * Sets the current indentation style to a new one.
     *
     * The value needs to be one of the `Lexer::INDENT_*` constants, but you can also just
     * pass either a single space or a single tab for the respective style.
     *
     * @param $indentStyle
     *
     * @return $this
     */
    public function setIndentStyle($indentStyle)
    {
        if (!in_array($indentStyle, [null, Lexer::INDENT_TAB, Lexer::INDENT_SPACE])) {
            throw new \InvalidArgumentException(
                'indentStyle needs to be null or one of the INDENT_* constants of the lexer'
            );
        }

        $this->indentStyle = $indentStyle;

        return $this;
    }

    /**
     * Returns the currently used indentation width.
     *
     * @return int
     */
    public function getIndentWidth()
    {
        return $this->indentWidth;
    }

    /**
     * Sets the currently used indentation width.
     *
     * The value of this specifies if e.g. 2 spaces make up one indentation level or 4.
     *
     * @param $indentWidth
     *
     * @return $this
     */
    public function setIndentWidth($indentWidth)
    {
        if (!is_null($indentWidth) &&
            (!is_int($indentWidth) || $indentWidth < 1)
        ) {
            throw new \InvalidArgumentException(
                'indentWidth needs to be null or an integer above 0'
            );
        }

        $this->indentWidth = $indentWidth;

        return $this;
    }

    /**
     * Runs all passed scanners once on the input string.
     *
     * The first scan that returns valid tokens will stop the scanning and
     * yields these tokens. If you want to continuously scan on something, rather
     * use the `loopScan`-method
     *
     * @param array|string $scanners the scanners to run
     *
     * @throws LexerException
     *
     * @return \Generator the generator yielding all tokens found
     */
    public function scan($scanners)
    {
        $scanners = $this->filterScanners($scanners);

        foreach ($scanners as $key => $scanner) {

            /** @var ScannerInterface $scanner */
            $success = false;
            foreach ($scanner->scan($this) as $token) {
                if (!($token instanceof TokenInterface)) {
                    $this->throwException(
                        'Scanner '.get_class($scanner).' generated a result that is not a '.TokenInterface::class
                    );
                }

                yield $token;
                $success = true;
            }

            if ($success) {
                return;
            }
        }
    }

    /**
     * Continuously scans with all scanners passed as the first argument.
     *
     * If the second argument is true, it will throw an exception if none of the scanners
     * produced any valid tokens. The reading also stops when the end of the input as been reached.
     *
     * @param $scanners
     * @param bool $required
     *
     * @throws LexerException
     *
     * @return \Generator
     */
    public function loopScan($scanners, $required = false)
    {
        while ($this->reader->hasLength()) {
            $success = false;
            foreach ($this->scan($scanners) as $token) {
                $success = true;
                yield $token;
            }

            if (!$success) {
                break;
            }
        }

        if ($this->reader->hasLength() && $required) {
            $this->throwException(
                'Unexpected '.$this->reader->peek(20)
            );
        }
    }

    public function createCurrentSourceLocation()
    {
        return new SourceLocation(
            $this->getOption('path'),
            $this->reader->getLine(),
            $this->reader->getOffset()
        );
    }

    /**
     * Creates a new instance of a token.
     *
     * The token automatically receives line/offset/level information through this method.
     *
     * @param string $className the class name of the token
     *
     * @return TokenInterface the token
     */
    public function createToken($className)
    {
        if (!is_subclass_of($className, TokenInterface::class)) {
            $this->throwException(
                "$className is not a valid token sub-class"
            );
        }

        return new $className(
            $this->createCurrentSourceLocation(),
            $this->level,
            str_repeat($this->getIndentStyle(), $this->getIndentWidth())
        );
    }

    /**
     * Quickly scans for a token by a single regular expression pattern.
     *
     * If the pattern matches, this method will yield a new token. If not, it will yield nothing
     *
     * All named capture groups are converted to `set*()`-methods, e.g.
     * `(?:<name>[a-z]+)` will automatically call `setName(<matchedValue>)` on the token.
     *
     * This method could be written without generators, but the way its designed is easier to use
     * in scanners as you can simply return it's value without having to check for it to be null.
     *
     *
     * @param $className
     * @param $pattern
     * @param null $modifiers
     *
     * @return \Generator
     */
    public function scanToken($className, $pattern, $modifiers = null)
    {
        if (!$this->reader->match($pattern, $modifiers)) {
            return;
        }

        $data = $this->reader->getMatchData();

        $token = $this->createToken($className);
        $this->reader->consume();
        foreach ($data as $key => $value) {
            $method = 'set'.ucfirst($key);

            if (method_exists($token, $method)) {
                call_user_func([$token, $method], $value);
            }
        }

        yield $this->endToken($token);
    }

    /**
     * @param TokenInterface $token
     *
     * @return TokenInterface
     */
    public function endToken(TokenInterface $token)
    {
        $token->getSourceLocation()->setOffsetLength(
            $this->reader->getOffset() - $token->getSourceLocation()->getOffset()
        );

        return $token;
    }

    /**
     * Filters and validates the passed scanners.
     *
     * This method makes sure that all scanners given are turned into their respective instances.
     *
     * @param $scanners
     *
     * @return array
     */
    private function filterScanners($scanners)
    {
        $scannerInstances = [];
        $scanners = is_array($scanners) ? $scanners : [$scanners];
        foreach ($scanners as $key => $scanner) {
            if (!is_a($scanner, ScannerInterface::class, true)) {
                throw new \InvalidArgumentException(
                    "The passed scanner with key `$key` doesn't seem to be either a valid ".ScannerInterface::class.
                    ' instance or extended class'
                );
            }

            $scannerInstances[] = $scanner instanceof ScannerInterface
                ? $scanner
                : new $scanner();
        }

        return $scannerInstances;
    }

    /**
     * Throws a lexer-exception.
     *
     * The current line and offset of the exception
     * get automatically appended to the message
     *
     * @param string     $message  A meaningful error message
     * @param int        $code     Error code
     * @param \Throwable $previous Source error
     *
     * @throws LexerException
     */
    public function throwException($message, $code = 0, $previous = null)
    {
        $pattern = "Failed to lex: %s \nNear: %s \nLine: %s \nOffset: %s";
        $path = $this->getOption('path');

        if ($path) {
            $pattern .= "\nPath: $path";
        }

        throw new LexerException(
            $this->createCurrentSourceLocation(),
            vsprintf($pattern, [
                $message,
                $this->reader->peek(20),
                $this->reader->getLine(),
                $this->reader->getOffset(),
            ]),
            $code,
            $previous
        );
    }
}

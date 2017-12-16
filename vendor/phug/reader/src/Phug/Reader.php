<?php

namespace Phug;

use Phug\Util\SourceLocation;

/**
 * A string reading utility that searches strings byte by byte.
 */
class Reader
{
    /**
     * An array of PREG errors with a good error message.
     *
     * @var array
     */
    private static $pregErrors = [
        PREG_NO_ERROR              => 'No error occured',
        PREG_INTERNAL_ERROR        => 'An internal error occured',
        PREG_BACKTRACK_LIMIT_ERROR => 'The backtrack limit was exhausted (Increase pcre.backtrack_limit in php.ini)',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted (Increase pcre.recursion_limit in php.ini)',
        PREG_BAD_UTF8_ERROR        => 'Bad UTF8 error',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Bad UTF8 offset error',
    ];

    /**
     * The default encoding to use while reading.
     *
     * @var string
     */
    protected $defaultEncoding = 'UTF-8';

    /**
     * Bad characters that are automatically removed when using `normalize()`.
     *
     * @var string
     */
    protected $badCharacters = "\0\r\v";

    /**
     * The characters that are seen as indentation.
     *
     * @var string
     */
    protected $indentCharacters = "\t ";

    /**
     * Characters that are seen as quote characters.
     *
     * @var string
     */
    protected $quoteCharacters = "\"'`";

    /**
     * An array of brackets that are seen as valid brackets for bracket counting.
     *
     * Key is always the open-bracket, value is the close-bracket.
     *
     * @var array
     */
    protected $expressionBrackets = [
        '(' => ')',
        '[' => ']',
        '{' => '}',
    ];

    /**
     * The current input string that is read.
     *
     * @var string
     */
    private $input;

    /**
     * The encoding currently used in this reading process.
     *
     * @var string
     */
    private $encoding;

    /**
     * The position in the input we are currently at.
     *
     * @var int
     */
    private $position;

    /**
     * Contains the line the reader is currently on.
     *
     * @var int
     */
    private $line;

    /**
     * Contains the offset of the line the reader is currently on.
     *
     * @var int
     */
    private $offset;

    /**
     * Contains the last result of `peek()`, if any.
     *
     * @var string|null
     */
    private $lastPeekResult;

    /**
     * Contains the last result-array of `match()`, if any.
     *
     * This is a normal array created by `preg_match`-matching.
     *
     * @var array|null
     */
    private $lastMatchResult;

    /**
     * The length that is to be consumed by `consume()`, if any.
     *
     * @var int|null
     */
    private $nextConsumeLength;

    private $inputLength;

    /**
     * Creates a new reader instance.
     *
     * @param string      $input    the input string to read from.
     * @param string|null $encoding the encoding used in the reading process.
     */
    public function __construct($input, $encoding = null)
    {
        $this->input = (string) $input;
        // Remove UTF-8 BOM
        if (substr($this->input, 0, 3) === pack('H*', 'EFBBBF')) {
            $this->input = substr($this->input, 3);
        }
        $this->encoding = $encoding ?: $this->defaultEncoding;
        $this->inputLength = mb_strlen($this->input, $this->encoding);

        $this->position = 0;
        $this->line = 1;
        $this->offset = 1;

        $this->lastPeekResult = null;
        $this->lastMatchResult = null;
        $this->nextConsumeLength = null;
    }

    /**
     * Returns the current input string.
     *
     * This doesn't equal the initial input string, as it's consumed byte by byte.
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Returns the currently used encoding.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Returns the last result of a `peek()`-call.
     *
     * @return string
     */
    public function getLastPeekResult()
    {
        return $this->lastPeekResult;
    }

    /**
     * Returns the last result of a `match()`-call.
     *
     * @return array
     */
    public function getLastMatchResult()
    {
        return $this->lastMatchResult;
    }

    /**
     * Returns the length that `consume()` should consume next.
     *
     * @return int
     */
    public function getNextConsumeLength()
    {
        return $this->nextConsumeLength;
    }

    /**
     * Returns the current position in our input string.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns the line the reader is currently on.
     *
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Returns the offset of the line the reader is currently on.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Removes useless characters from the whole input string.
     *
     * @return $this
     */
    public function normalize()
    {
        $this->input = str_replace(str_split($this->badCharacters), '', $this->input);
        $this->inputLength = mb_strlen($this->input, $this->encoding);

        return $this;
    }

    /**
     * Returns the total length of the remaining input string.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->inputLength;
    }

    /**
     * Returns wether the input string still has characters remaining.
     *
     * @return bool
     */
    public function hasLength()
    {
        return $this->inputLength !== 0;
    }

    /**
     * Peeks one or multiple characters without moving the pointer forward.
     *
     * The peeked length will be stored and can be consumed with `consume()` later on.
     *
     * @param int $length the length to consume (default: 1).
     * @param int $start  the offset to start on based on the current offset (default: 0).
     *
     * @return string|null the peeked string or null if reading is finished.
     */
    public function peek($length = null, $start = null)
    {
        $this->lastPeekResult = null;
        $this->nextConsumeLength = null;

        if (!$this->hasLength()) {
            return;
        }

        $length = $length !== null ? $length : 1;
        $start = $start !== null ? $start : 0;

        if (!is_int($length) || $length < 1) {
            throw new \InvalidArgumentException(
                'Failed to peek: Length should be a number above 1'
            );
        }

        //Cap read length to the size of this document
        if ($length > ($maxLength = $this->getLength())) {
            $length = $maxLength;
        }

        $this->lastPeekResult = mb_substr($this->input, $start, $length, $this->encoding);
        $this->nextConsumeLength = $start + $length;

        return $this->lastPeekResult;
    }

    /**
     * Matches current input string against a regular expression.
     *
     * The result length will be stored and can be consumed with `consume()` later on.
     *
     * Notice that ^ is automatically prepended to the pattern.
     *
     * @param string $pattern         the regular expression without slashes or modifiers.
     * @param string $modifiers       the modifiers for the regular expression.
     * @param string $ignoredSuffixes characters that are scanned, but don't end up in the consume length.
     *
     * @throws ReaderException
     *
     * @return bool wether the expression matched or not.
     */
    public function match($pattern, $modifiers = null, $ignoredSuffixes = null)
    {
        $modifiers = $modifiers ?: '';
        $ignoredSuffixes = $ignoredSuffixes ?: "\n";
        $matches = null;
        $this->lastMatchResult = null;
        $this->nextConsumeLength = null;

        $result = preg_match(
            "/^$pattern/$modifiers",
            $this->input,
            $matches
        );

        if ($result === false) {
            $this->throwException(
                'Failed to match pattern: '.$this->getPregErrorText()
            );
        }

        if ($result === 0) {
            return false;
        }

        $this->lastMatchResult = $matches;
        $this->nextConsumeLength = mb_strlen(rtrim($this->lastMatchResult[0], $ignoredSuffixes));

        return true;
    }

    /**
     * Returns a single capture group matched with `match()` based on its index or name.
     *
     * @param string|int $key the index or name of the capturing group.
     *
     * @return string|null the matched string part.
     */
    public function getMatch($key)
    {
        if (!$this->lastMatchResult) {
            $this->throwException(
                "Failed to get match $key: No match result found. Use match first"
            );
        }

        return isset($this->lastMatchResult[$key])
            ? $this->lastMatchResult[$key]
            : null;
    }

    /**
     * Returns all named capturing groups matched with `match()` in an array.
     *
     * @return array the matched string parts indexed by capturing group name.
     */
    public function getMatchData()
    {
        if (!$this->lastMatchResult) {
            $this->throwException(
                'Failed to get match data: No match result found. Use match first'
            );
        }

        $data = [];
        foreach ($this->lastMatchResult as $key => $value) {
            if (is_string($key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Consumes part of the input string and advances internal counters.
     *
     * When no length is given, it will use the last `peek()` or `match()` length automatically.
     * Use this after successful `peek()` or `match()`-operations.
     *
     * @param int $length the length to consume (default: null)
     *
     * @return string
     */
    public function consume($length = null)
    {
        $length = $length ?: $this->nextConsumeLength;

        if ($length === null) {
            $this->throwException(
                'Failed to consume: No length given. Peek or match first.'
            );
        }

        $consumedPart = mb_substr($this->input, 0, $length, $this->encoding);
        $this->input = substr($this->input, strlen($consumedPart));
        $this->inputLength -= $length;
        $this->position += $length;
        $this->offset += $length;

        //Check for new-lines in consumed part to increase line and offset correctly
        $newLines = mb_substr_count($consumedPart, "\n");
        $this->line += $newLines;

        if ($newLines) {
            //if we only have one new-line character, the new offset is 0
            //Else the offset is the length of the last line read - 1
            if (mb_strlen($consumedPart, $this->encoding) === 1) {
                $this->offset = 1;
            } else {
                $parts = explode("\n", $consumedPart);
                $this->offset = mb_strlen($parts[count($parts) - 1], $this->encoding);
            }
        }

        $this->nextConsumeLength = null;
        $this->lastPeekResult = null;
        $this->lastMatchResult = null;

        return $consumedPart;
    }

    /**
     * Reads part of a string until it doesn't match the given callback anymore.
     *
     * The string part is consumed directly, no `consume()` is required after `read()`-operations.
     *
     * @param callable $callback   the callback to check string parts against.
     * @param int      $peekLength the length to peek for each iteration. (default: 1)
     *
     * @return string|null the result string or null if finished reading.
     */
    public function readWhile($callback, $peekLength = null)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(
                'Argument 1 passed to Reader->readWhile needs to be callback'
            );
        }

        if (!$this->hasLength()) {
            return;
        }

        if ($peekLength === null) {
            $peekLength = 1;
        }

        $result = '';
        while ($this->hasLength() && call_user_func($callback, $this->peek($peekLength))) {
            $result .= $this->consume();
        }

        return $result;
    }

    /**
     * The opposite of `readWhile()`. Reads a string until the callback matches the string part.
     *
     * @param callable $callback   the callback to check string parts against.
     * @param int      $peekLength the length to peek for each iteration. (default: 1)
     *
     * @return string|null the result string or null if finished reading.
     */
    public function readUntil($callback, $peekLength = null)
    {
        return $this->readWhile(function ($char) use ($callback) {
            return !call_user_func($callback, $char);
        }, $peekLength);
    }

    /**
     * Peeks one byte and checks if it equals the given character.
     *
     * @param string $char the character to check against.
     *
     * @return bool whether it matches or not.
     */
    public function peekChar($char)
    {
        return $this->peek() === $char;
    }

    /**
     * Peeks one byte and checks if it equals the given characters.
     *
     * You can pass the characters as a string containing them all or as an array.
     *
     * @param string|array $chars the characters to check against.
     *
     * @return bool whether one of them match or not.
     */
    public function peekChars($chars)
    {
        return in_array($this->peek(), is_array($chars) ? $chars : str_split($chars), true);
    }

    /**
     * Peeks and checks if it equals the given string.
     *
     * @param string $string the string to check against.
     *
     * @return bool whether it matches or not.
     */
    public function peekString($string)
    {
        return $this->peek(mb_strlen($string)) === $string;
    }

    /**
     * Peeks one byte and checks if it is a newline character.
     *
     * @return bool whether it matches or not.
     */
    public function peekNewLine()
    {
        return $this->peekChars("\n");
    }

    /**
     * Peeks one byte and checks if it is an indentation character.
     *
     * The indentation characters are defined in Reader->indentCharacters
     *
     * @return bool whether it is one or not.
     */
    public function peekIndentation()
    {
        return $this->peekChars($this->indentCharacters);
    }

    /**
     * Peeks one byte and checks if it is a quote character.
     *
     * The quote characters are defined in Reader->quoteCharacters
     *
     * @return bool whether it is one or not.
     */
    public function peekQuote()
    {
        return $this->peekChars($this->quoteCharacters);
    }

    /**
     * Peeks one byte and checks if it is a whitespace character.
     *
     * Uses ctype_space() internally.
     *
     * @return bool whether it is one or not.
     */
    public function peekSpace()
    {
        return ctype_space($this->peek());
    }

    /**
     * Peeks one byte and checks if it is a digit character.
     *
     * Uses ctype_digit() internally.
     *
     * @return bool whether it is one or not.
     */
    public function peekDigit()
    {
        return ctype_digit($this->peek());
    }

    /**
     * Peeks one byte and checks if it is a alphabetical character.
     *
     * Uses ctype_alpha() internally.
     *
     * @return bool whether it is one or not.
     */
    public function peekAlpha()
    {
        return ctype_alpha($this->peek());
    }

    /**
     * Peeks one byte and checks if it is a alpha-numeric character.
     *
     * Uses ctype_alnum() internally.
     *
     * @return bool whether it is one or not.
     */
    public function peekAlphaNumeric()
    {
        return ctype_alnum($this->peek());
    }

    /**
     * Peeks one byte and checks if it could be a valid alphabetical identifier.
     *
     * @param array $allowedChars additional chars to allow in the identifier (default: ['_'])
     *
     * @return bool whether it could be or not.
     */
    public function peekAlphaIdentifier(array $allowedChars = null)
    {
        $allowedChars = $allowedChars ?: ['_'];

        return $this->peekAlpha() || $this->peekChars($allowedChars);
    }

    /**
     * Peeks one byte and checks if it could be a valid alpha-numeric identifier.
     *
     * @param array $allowedChars additional chars to allow in the identifier (default: ['_'])
     *
     * @return bool whether it could be or not.
     */
    public function peekIdentifier(array $allowedChars = null)
    {
        return $this->peekAlphaIdentifier($allowedChars) || $this->peekDigit();
    }

    /**
     * Reads all upcoming indentation characters in a string using `peekIndentation()`.
     *
     * @return string|null the indentation string part or null if no indentation encountered.
     */
    public function readIndentation()
    {
        if (!$this->peekIndentation()) {
            return;
        }

        return $this->readWhile([$this, 'peekIndentation']);
    }

    /**
     * Reads a whole line and returns it.
     *
     * @return string the line until the new-line character.
     */
    public function readUntilNewLine()
    {
        return $this->readUntil([$this, 'peekNewLine']);
    }

    /**
     * Reads all upcoming whitespace characters in a string using `ctype_space()`.
     *
     * @return string|null the whitespace string part or null if no whitespace encountered.
     */
    public function readSpaces()
    {
        if (!$this->peekSpace()) {
            return;
        }

        return $this->readWhile('ctype_space');
    }

    /**
     * Reads all upcoming digit characters in a string using `ctype_digit()`.
     *
     * @return string|null the digit string part or null if no digits encountered.
     */
    public function readDigits()
    {
        if (!$this->peekDigit()) {
            return;
        }

        return $this->readWhile('ctype_digit');
    }

    /**
     * Reads all upcoming alphabetical characters in a string using `ctype_alpha()`.
     *
     * @return string|null the alphabetical string part or null if no alphabetical encountered.
     */
    public function readAlpha()
    {
        if (!$this->peekAlpha()) {
            return;
        }

        return $this->readWhile('ctype_alpha');
    }

    /**
     * Reads all upcoming alpha-numeric characters in a string using `ctype_alnum()`.
     *
     * @return string|null the alpha-numeric string part or null if no alpha-numeric encountered.
     */
    public function readAlphaNumeric()
    {
        if (!$this->peekAlphaNumeric()) {
            return;
        }

        return $this->readWhile('ctype_alnum');
    }

    /**
     * Reads an upcoming alpha-numeric identifier in a string.
     *
     * Identifiers start with an alphabetical character and then follow with alpha-numeric characters.
     *
     * @param string $prefix       the prefix for an identifier (e.g. $, @, % etc., default: none)
     * @param array  $allowedChars additional chars to allow in the identifier (default: ['_'])
     *
     * @return string|null the resulting identifier or null of none encountered.
     */
    public function readIdentifier($prefix = null, $allowedChars = null)
    {
        if ($prefix) {
            if ($this->peek(mb_strlen($prefix)) !== $prefix) {
                return;
            }

            $this->consume();
        } elseif (!$this->peekAlphaIdentifier($allowedChars)) {
            return;
        }

        return $this->readWhile(function () use ($allowedChars) {
            return $this->peekIdentifier($allowedChars);
        });
    }

    /**
     * Reads an enclosed string correctly.
     *
     * Strings start with a quote and end with that same quote while other quotes inside it
     * are ignored, including other kinds of expressions.
     *
     * The quote itself is automatically passed as an escape sequence, so a '-enclosed string always knows \' as
     * an escape expression.
     *
     * @param bool $raw whether to return the string raw, with quotes and keep escape sequences intact.
     *
     * @return string|null the resulting string or null if none encountered.
     */
    public function readString(array $escapeSequences = null, $raw = false)
    {
        if (!$this->peekQuote()) {
            return;
        }

        $quoteStyle = $this->consume();

        $char = null;
        $string = '';
        $closed = false;
        while ($this->hasLength()) {
            $char = $this->peek();
            $this->consume();

            //Handle escaping based on passed sequences
            if ($char === '\\') {
                $nextChar = $this->consume(1);
                $string .= array_key_exists($nextChar, $escapeSequences ?: [])
                    //Peek the escape sequence
                    ? $escapeSequences[$nextChar]
                    //Peek the next char
                    : ($raw ? $char : '').$nextChar;
                continue;
            }

            //End the string (Escaped quotes have already been handled)
            if ($char === $quoteStyle) {
                $closed = true;

                break;
            }

            $string .= $char;
        }

        if (!$closed) {
            $this->throwException(
                "Unclosed string ($quoteStyle) encountered"
            );
        }

        if ($raw) {
            $string = $quoteStyle.$string.$quoteStyle;
        }

        return $string;
    }

    /**
     * Reads a code-expression that applies bracket counting correctly.
     *
     * The expression reading stops on any string specified in the `$breaks`-argument.
     * Breaks will be ignored if we are still in an open bracket.
     *
     * Notice that this also validates brackets, if any bracket set doesn't match, this
     * will throw an exception (e.g. "callMe(['demacia')]" would throw an exception)
     *
     * @param array $breaks   the break characters to use (Breaks on string end by default).
     * @param array $brackets the brackets to allow (Defaulting to whatever is defined in Reader->expressionBrackets)
     *
     * @return string|null the resulting expression or null, if none encountered.
     */
    public function readExpression(array $breaks = null, array $brackets = null)
    {
        if (!$this->hasLength()) {
            return;
        }

        $breaks = $breaks ?: [];
        $brackets = $brackets ?: $this->expressionBrackets;
        $expression = '';
        $char = null;
        $bracketStack = [];
        while ($this->hasLength()) {
            //Append a string if any was found
            //Notice there can be brackets in strings, we dont want to
            //count those
            $expression .= $this->readString(null, true);

            if (!$this->hasLength()) {
                break;
            }

            //Check for breaks
            if (count($bracketStack) === 0) {
                foreach ($breaks as $break) {
                    if ($this->peekString($break)) {
                        break 2;
                    }
                }
            }

            //Count brackets
            $char = $this->peek();
            if (in_array($char, array_keys($brackets), true)) {
                $bracketStack[] = $char;
            } elseif (in_array($char, array_values($brackets), true)) {
                if (count($bracketStack) < 1) {
                    $this->throwException(
                        "Unexpected bracket $char encountered, no brackets open"
                    );
                }

                $last = count($bracketStack) - 1;
                if ($char !== $brackets[$bracketStack[$last]]) {
                    $this->throwException(
                        "Unclosed bracket {$bracketStack[$last]} encountered, "
                        ."got $char instead"
                    );
                }

                array_pop($bracketStack);
            }

            $expression .= $char;
            $this->consume();
        }

        if (count($bracketStack) > 0) {
            $this->throwException(
                'Unclosed brackets '.implode(', ', $bracketStack).' encountered '
                .'at end of expression'
            );
        }

        return trim($expression);
    }

    /**
     * Returns a describing text for the last PREG error that happened.
     *
     * @param null $code
     *
     * @return string
     */
    protected function getPregErrorText($code = null)
    {
        $code = $code ?: preg_last_error();

        if (!isset(self::$pregErrors[$code])) {
            $code = PREG_NO_ERROR;
        }

        return self::$pregErrors[$code];
    }

    /**
     * Throws an exception that contains useful debugging information.
     *
     * @param string $message the message to pass to the exception.
     *
     * @throws ReaderException
     */
    protected function throwException($message)
    {
        $exception = new ReaderException(
            new SourceLocation(null, $this->line, $this->offset),
            sprintf(
                "Failed to read: %s \nNear: %s \nLine: %s \nOffset: %s \nPosition: %s",
                $message,
                $this->peek(20),
                $this->line,
                $this->offset,
                $this->position
            )
        );

        throw $exception;
    }
}

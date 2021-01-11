<?php

namespace Phug;

use Phug\Lexer\Event\EndLexEvent;
use Phug\Lexer\Event\LexEvent;
use Phug\Lexer\Event\TokenEvent;
use Phug\Lexer\HandleTokenInterface;
use Phug\Lexer\Partial\DumpTokenTrait;
use Phug\Lexer\Partial\StateTrait;
use Phug\Lexer\Scanner\TextScanner;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\TokenInterface;
use Phug\Util\Collection;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\ModuleContainerTrait;

/**
 * Performs lexical analysis and provides a token generator.
 *
 * Tokens are defined as single units of code
 * (e.g. tag, class, id, attributeStart, attribute, attributeEnd)
 *
 * These will run through the parser and be converted to an AST
 *
 * The lexer works sequentially, `lex()` will return a generator and
 * you can read that generator in any manner you like.
 * The generator will produce valid tokens until the end of the passed
 * input.
 *
 * Usage example:
 * <code>
 *
 *     use Phug\Lexer;
 *
 *     $lexer = new Lexer();
 *
 *     foreach ($lexer->lex($pugInput) as $token)
 *          var_dump($token);
 *
 * </code>
 */
class Lexer implements LexerInterface, ModuleContainerInterface
{
    use ModuleContainerTrait;
    use StateTrait;
    use DumpTokenTrait;

    const INDENT_SPACE = ' ';
    const INDENT_TAB = "\t";
    const DEFAULT_TAB_WIDTH = 4;

    /**
     * @var TokenInterface
     */
    private $lastToken;

    /**
     * @var TokenInterface|null
     */
    private $previousToken;

    /**
     * Creates a new lexer instance.
     *
     * The options should be an associative array
     *
     * Valid options are:
     *
     * lexer_state_class_name:  The class of the lexer state to use
     * level:           The internal indentation level to start on
     * indent_style:     The indentation character (auto-detected)
     * indent_width:     How often to repeat indentStyle (auto-detected)
     * encoding:        The encoding when working with mb_*-functions (auto-detected)
     * scanners:        An array of scans that will be performed
     *
     * Add a new scan to 'scans' to extend the lexer.
     * Notice that the parser needs to be able to handle newly introduced tokens provided by scanners.
     *
     * @param array|null $options the options passed to the lexer instance
     *
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        $this->setOptionsDefaults($options ?: [], [
            'lexer_state_class_name'   => State::class,
            'level'                    => 0,
            'indent_style'             => null,
            'indent_width'             => null,
            'allow_mixed_indent'       => true,
            'multiline_interpolation'  => true,
            'multiline_markup_enabled' => true,
            'encoding'                 => null,
            'lexer_modules'            => [],
            'keywords'                 => [],
            'scanners'                 => Scanners::getList(),
            'mixin_keyword'            => 'mixin',
            'mixin_call_keyword'       => '\\+',

            //Events
            'on_lex'                   => null,
            'on_lex_end'               => null,
            'on_token'                 => null,
        ]);

        $this->state = null;

        $this->updateOptions();
    }

    /**
     * @return TokenInterface|null
     */
    public function getPreviousToken()
    {
        return $this->previousToken;
    }

    /**
     * Synchronize the lexer to the new options values.
     */
    public function updateOptions()
    {
        if ($onLex = $this->getOption('on_lex')) {
            $this->attach(LexerEvent::LEX, $onLex);
        }

        if ($onLex = $this->getOption('on_lex_end')) {
            $this->attach(LexerEvent::END_LEX, $onLex);
        }

        if ($onToken = $this->getOption('on_token')) {
            $this->attach(LexerEvent::TOKEN, $onToken);
        }

        $this->addModules($this->getOption('lexer_modules'));
    }

    /**
     * Returns the current scanners registered for the lexing process.
     *
     * @return ScannerInterface[]
     */
    public function getScanners()
    {
        return $this->getOption('scanners');
    }

    /**
     * Adds a new scanner class to use in the lexing process at the top of scanning order.
     *
     * The scanner class needs to extend Phug\Lexer\ScannerInterface. It can be the class name itself
     * or an instance of it.
     *
     * @param string                  $name
     * @param ScannerInterface|string $scanner
     *
     * @return $this
     */
    public function prependScanner($name, $scanner)
    {
        $this->filterScanner($scanner);

        $scanners = [
            $name => $scanner,
        ];

        foreach ($this->getScanners() as $scannerName => $classNameOrInstance) {
            if ($scannerName !== $name) {
                $scanners[$scannerName] = $classNameOrInstance;
            }
        }

        return $this->setOption('scanners', $scanners);
    }

    /**
     * Adds a new scanner class to use in the lexing process.
     *
     * The scanner class needs to extend Phug\Lexer\ScannerInterface. It can be the class name itself
     * or an instance of it.
     *
     * @param string                  $name
     * @param ScannerInterface|string $scanner
     *
     * @return $this
     */
    public function addScanner($name, $scanner)
    {
        $this->filterScanner($scanner);

        return $this->setOption(['scanners', $name], $scanner);
    }

    /**
     * Returns a generator that will lex the passed input sequentially.
     *
     * If you don't move the generator, the lexer does nothing.
     * Only as soon as you iterate the generator or call `next()`/`current()` on it
     * the lexer will start its work and spit out tokens sequentially.
     * This approach requires less memory during the lexing process.
     *
     * The returned tokens are required to be `Phug\Lexer\TokenInterface` instances.
     *
     * @param string $input the pug-string to lex into tokens.
     * @param null   $path
     *
     * @return iterable a generator that can be iterated sequentially
     */
    public function lex($input, $path = null)
    {
        $stateClassName = $this->getOption('lexer_state_class_name');
        $lexEvent = new LexEvent($input, $path, $stateClassName, [
            'encoding'                 => $this->getOption('encoding'),
            'indent_style'             => $this->getOption('indent_style'),
            'indent_width'             => $this->getOption('indent_width'),
            'allow_mixed_indent'       => $this->getOption('allow_mixed_indent'),
            'multiline_interpolation'  => $this->getOption('multiline_interpolation'),
            'multiline_markup_enabled' => $this->getOption('multiline_markup_enabled'),
            'level'                    => $this->getOption('level'),
            'mixin_keyword'            => $this->getRegExpOption('mixin_keyword'),
            'mixin_call_keyword'       => $this->getRegExpOption('mixin_call_keyword'),
        ]);

        $this->trigger($lexEvent);

        $input = $lexEvent->getInput();
        $path = $lexEvent->getPath();
        $stateClassName = $lexEvent->getStateClassName();
        $stateOptions = $lexEvent->getStateOptions();

        $stateOptions['path'] = $path;
        $stateOptions['keyword_names'] = array_keys($this->getOption('keywords') ?: []);

        if (!is_a($stateClassName, State::class, true)) {
            throw new \InvalidArgumentException(
                'lexer_state_class_name needs to be a valid '.State::class.' sub class, '.
                $stateClassName.' given'
            );
        }

        //Put together our initial state
        $this->state = new $stateClassName($this, $input, $stateOptions);

        $scanners = $this->getOption('scanners');

        //We always scan for text at the very end.
        $scanners['final_plain_text'] = TextScanner::class;

        //Scan for tokens
        //N> yield from $this->handleTokens($this->state->loopScan($scanners));
        $tokens = $this->state->loopScan($scanners);

        foreach ($this->handleTokens($tokens) as $token) {
            yield $token;
        }

        $this->trigger(new EndLexEvent($lexEvent));

        //Free state
        $this->state = null;
        $this->previousToken = null;
        $this->lastToken = null;
    }

    /**
     * @param TokenInterface $lastToken
     */
    private function setLastToken($lastToken)
    {
        $this->previousToken = $this->lastToken;
        $this->lastToken = $lastToken;
    }

    private function getRegExpOption($name)
    {
        $value = $this->getOption($name);

        return is_array($value) ? '(?:'.implode('|', $value).')' : $value;
    }

    private function proceedTokenEvent($token)
    {
        $event = new TokenEvent($token);

        if (!($token instanceof HandleTokenInterface) || !$token->isHandled()) {
            $this->trigger($event);

            if ($token instanceof HandleTokenInterface) {
                $token->markAsHandled();
            }
        }

        return $event;
    }

    private function handleToken($token)
    {
        $event = $this->proceedTokenEvent($token);
        $tokens = $event->getTokenGenerator();

        if ($tokens) {
            //N> yield from $this->handleTokens($tokens)
            foreach ($this->handleTokens($tokens) as $tok) {
                $this->setLastToken($tok);

                yield $tok;
            }

            return;
        }

        $token = $event->getToken();
        $this->setLastToken($token);

        yield $token;
    }

    /**
     * @param iterable $tokens
     *
     * @return iterable
     */
    private function handleTokens($tokens)
    {
        foreach ($tokens as $rawToken) {
            foreach ($this->handleToken($rawToken) as $token) {
                yield $token;
            }
        }
    }

    /**
     * @return TokenInterface
     */
    public function getLastToken()
    {
        return $this->lastToken;
    }

    public function dump($input)
    {
        if ($input instanceof TokenInterface) {
            return $this->dumpToken($input);
        }

        if (!Collection::isIterable($input)) {
            $input = $this->lex((string) $input);
        }

        $dumped = '';
        foreach ($input as $token) {
            $dumped .= $this->dump($token);
        }

        return $dumped;
    }

    private function filterScanner($scanner)
    {
        if (!is_subclass_of($scanner, ScannerInterface::class)) {
            throw new \InvalidArgumentException(
                "Scanner $scanner is not a valid ".ScannerInterface::class.' instance or extended class'
            );
        }
    }

    public function getModuleBaseClassName()
    {
        return LexerModuleInterface::class;
    }
}

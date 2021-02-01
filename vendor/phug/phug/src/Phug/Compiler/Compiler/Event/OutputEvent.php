<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;

class OutputEvent extends Event
{
    private $compileEvent;
    private $output;

    protected $openPhpTag = '<?php ';
    protected $closePhpTag = ' ?>';

    /**
     * OutputEvent constructor.
     *
     * @param CompileEvent $compileEvent
     * @param string       $output
     */
    public function __construct(CompileEvent $compileEvent, $output)
    {
        parent::__construct(CompilerEvent::OUTPUT);

        $this->compileEvent = $compileEvent;
        $this->output = $output;
    }

    /**
     * @return CompileEvent
     */
    public function getCompileEvent()
    {
        return $this->compileEvent;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Prepend PHP code before the output (but after the namespace statement if present).
     *
     * @param string $code PHP code (without <?php ?> tags).
     */
    public function prependCode($code)
    {
        return $this->prependOutput($this->closePhpCode($this->openPhpCode($code)));
    }

    /**
     * Prepend output (HTML code or PHP code with <?php ?> tags) before the output (but after the namespace statement
     * if present).
     *
     * @param string $code HTML code or PHP code with <?php ?> tags.
     */
    public function prependOutput($code)
    {
        $this->setOutput(
            $this->hasNamespaceStatement($namespaceStatement, $output)
                ? $this->concatCode(
                    $this->closePhpCode($namespaceStatement),
                    $code,
                    $this->openPhpCode($output)
                )
                : $this->concatCode($code, $this->output)
        );
    }

    /**
     * Check if the output contains a namespace statement at the beginning.
     *
     * @param string $namespaceStatement Variable passed by reference that receives the namespace statement if present,
     *                                   or an empty string else.
     * @param string $afterCode          Variable passed by reference that receives the rest of the output (all the
     *                                   output if no namespace statement present).
     *
     * @return bool true if a namespace statement if present, false else.
     */
    public function hasNamespaceStatement(&$namespaceStatement = '', &$afterCode = '')
    {
        if (preg_match('/^(<\?(?:php)?\s+namespace\s\S.*)(((?:;|\n|\?>)[\s\S]*)?)$/U', $this->output, $matches)) {
            if (substr($matches[2], 0, 1) === ';') {
                $matches[1] .= ';';
                $matches[2] = substr($matches[2], 1);
            }

            $namespaceStatement = $matches[1];
            $afterCode = $matches[2];

            return true;
        }

        $afterCode = $this->output;

        return false;
    }

    protected function openPhpCode($code)
    {
        $trimmedCode = ltrim($code);
        $trimmedCloseTag = ltrim($this->closePhpTag);
        $closeLength = strlen($trimmedCloseTag);

        return substr($trimmedCode, 0, $closeLength) === $trimmedCloseTag
            ? substr($trimmedCode, $closeLength)
            : $this->openPhpTag.$code;
    }

    protected function closePhpCode($code)
    {
        $trimmedCode = rtrim($code);
        $trimmedOpenTag = rtrim($this->openPhpTag);
        $openLength = strlen($trimmedOpenTag);

        return substr($trimmedCode, -$openLength) === $trimmedOpenTag
            ? substr($trimmedCode, 0, -$openLength)
            : $code.$this->closePhpTag;
    }

    protected function concatCode()
    {
        $string = '';
        $closeLength = strlen($this->closePhpTag);
        $openLength = strlen($this->openPhpTag);

        foreach (func_get_args() as $nextString) {
            if (substr($string, -$closeLength) === $this->closePhpTag &&
                substr($nextString, 0, $openLength) === $this->openPhpTag) {
                $string = rtrim(substr($string, 0, -$closeLength))."\n";
                $nextString = ltrim(substr($nextString, $openLength));
            }

            $string .= $nextString;
        }

        return $string;
    }
}

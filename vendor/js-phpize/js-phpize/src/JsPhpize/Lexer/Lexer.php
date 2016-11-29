<?php

namespace JsPhpize\Lexer;

use JsPhpize\JsPhpize;

class Lexer extends Scanner
{
    /**
     * @var string
     */
    protected $input;

    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $fileInfo = null;

    /**
     * @var string
     */
    protected $consumed = '';

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $this->engine = $engine;
        $this->filename = $filename;
        $this->line = 1;
        $disallow = $engine->getOption('disallow', array());
        if (is_string($disallow)) {
            $disallow = explode(' ', $disallow);
        }
        $this->disallow = array_map('strtolower', (array) $disallow);
        $this->input = trim($input);
    }

    public function exceptionInfos()
    {
        if (is_null($this->fileInfo)) {
            $this->fileInfo = $this->filename ? ' in ' . realpath($this->filename) : '';
        }

        return
            $this->fileInfo .
            ' on line ' . $this->line .
            ' near from ' . trim($this->consumed);
    }

    protected function consume($consumed)
    {
        $consumed = is_int($consumed) ? substr($this->input, 0, $consumed) : $consumed;
        $this->consumed = strlen(trim($consumed)) > 1 ? $consumed : $this->consumed . $consumed;
        $this->line += substr_count($consumed, "\n");
        $this->input = substr($this->input, strlen($consumed));
    }

    protected function token($type, $data = array())
    {
        return new Token($type, is_string($data) ? array('value' => $data) : (array) $data);
    }

    protected function typeToken($matches)
    {
        $this->consume($matches[0]);

        return $this->token(trim($matches[0]));
    }

    protected function valueToken($type, $matches)
    {
        $this->consume($matches[0]);

        return $this->token($type, trim($matches[0]));
    }

    protected function scan($pattern, $method)
    {
        if (preg_match('/^\s*(' . $pattern . ')/', $this->input, $matches)) {
            return $this->{'scan' . ucfirst($method)}($matches);
        }
    }

    public function next()
    {
        if (!strlen($this->input)) {
            return;
        }

        $patterns = array(
            '\n' => 'newline',
            '\/\/.*?\n|\/\*[\s\S]*?\*\/' => 'comment',
            '"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'' => 'string',
            '0[bB][01]+|0[oO][0-7]+|0[xX][0-9a-fA-F]+|(\d+(\.\d*)?|\.\d+)([eE]-?\d+)?' => 'number',
            '=>' => 'lambda',
            'delete|typeof|void' => 'operator',
            '>>>=|<<=|>>=|\*\*=' => 'operator',
            '\\+\\+|--|\\&\\&|\\|\\||\\*\\*|>>>|<<|>>' => 'operator',
            '===|!==|>=|<=|<>|!=|==|>|<' => 'operator',
            '[\\|\\^&%\\/\\*\\+\\-]=' => 'operator',
            '[\\[\\]\\{\\}\\(\\)\\:\\.\\/\\*~\\!\\^\\|&%\\?,;\\+\\-]' => 'operator',
            '(?<![a-zA-Z0-9\\\\_\\x7f-\\xff])(as|async|await|break|case|catch|class|const|continue|debugger|default|do|else|enum|export|extends|finally|for|from|function|get|if|implements|import|in|instanceof|interface|let|new|of|package|private|protected|public|return|set|static|super|switch|throw|try|var|while|with|yield\*?)(?![a-zA-Z0-9\\\\_\\x7f-\\xff])' => 'keyword',
            '(?<![a-zA-Z0-9\\\\_\\x7f-\\xff])(null|undefined|Infinity|NaN|true|false|Math\.[A-Z][A-Z0-9_]*|[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*|[\\\\\\x7f-\\xff_][A-Z0-9\\\\_\\x7f-\\xff]*[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*)(?![a-zA-Z0-9\\\\_\\x7f-\\xff])' => 'constant',
            '(?<![a-zA-Z0-9\\\\_\\x7f-\\xff\\$])[a-zA-Z\\\\\\x7f-\\xff\\$_][a-zA-Z0-9\\\\_\\x7f-\\xff\\$]*(?![a-zA-Z0-9\\\\_\\x7f-\\xff\\$])' => 'variable',
            '[\\s\\S]' => 'operator',
        );

        foreach ($patterns as $pattern => $method) {
            if ($token = $this->scan($pattern, $method)) {
                if (in_array($method, $this->disallow)) {
                    throw new Exception($method . ' is disallowed.', 3);
                }

                return $token;
            }
        }
    }
}

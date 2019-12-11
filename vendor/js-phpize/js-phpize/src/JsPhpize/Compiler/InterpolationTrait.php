<?php

namespace JsPhpize\Compiler;

use JsPhpize\Parser\Parser;

trait InterpolationTrait
{
    protected function readInterpolation($value)
    {
        while (strlen($value)) {
            preg_match('/^([\s\S]*)(?:(\\\\|\\${)[\s\S]*)?$/U', $value, $match);

            yield var_export($match[1], true);

            $value = mb_substr($value, mb_strlen($match[1]));

            if (isset($match[2])) {
                if ($match[2] === '\\') {
                    yield var_export(mb_substr($value, 1, 1), true);

                    $value = mb_substr($value, 2);

                    continue;
                }

                $value = mb_substr($value, 2);

                $parser = new Parser($this->engine, $value, $this->filename);

                yield rtrim($this->visitInstruction($parser->parse()->instructions[0], ''), ";\t\n\r\0\x0B ");

                $value = $parser->rest();
            }
        }
    }
}

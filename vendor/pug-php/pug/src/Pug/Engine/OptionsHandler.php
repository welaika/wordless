<?php

namespace Pug\Engine;

use Phug\Compiler\Event\OutputEvent;
use Phug\Lexer\Event\LexEvent;

abstract class OptionsHandler extends PugJsEngine
{
    /**
     * Built-in filters.
     *
     * @var array
     */
    protected $optionsAliases = [
        'cache'            => 'cachedir',
        'prettyprint'      => 'pretty',
        'allowMixedIndent' => 'allow_mixed_indent',
        'keepBaseName'     => 'keep_base_name',
        'notFound'         => 'not_found_template',
        'customKeywords'   => 'keywords',
    ];

    protected function normalizeOptionName($name)
    {
        return isset($this->optionsAliases[$name])
            ? $this->optionsAliases[$name]
            : str_replace('_', '', strtolower($name));
    }

    protected function copyNormalizedOptions(&$options)
    {
        foreach ($options as $key => $value) {
            $lowerKey = $this->normalizeOptionName($key);
            if ($lowerKey !== $key) {
                $options[$lowerKey] = $value;
            }
        }
    }

    protected function setUpOptionNameHandlers()
    {
        $this->addOptionNameHandlers(function ($name) {
            return is_array($name)
                ? array_map([$this, 'normalizeOptionName'], $name)
                : $this->normalizeOptionName($name);
        });
    }

    protected function setUpPreRender(&$options)
    {
        if (isset($options['preRender'])) {
            $preRender = $options['preRender'];
            $onLex = isset($options['on_lex']) ? $options['on_lex'] : null;
            $options['on_lex'] = function (LexEvent $event) use ($onLex, $preRender) {
                if ($onLex) {
                    call_user_func($onLex, $event);
                }
                $event->setInput(call_user_func($preRender, $event->getInput()));
            };
        }
    }

    protected function shiftNamespaceOffsets($token, $afterNamespace, &$start, &$end)
    {
        $length = mb_strlen($token);
        if (!$afterNamespace) {
            $start += $length;
        }
        $end += $length;
    }

    protected function filterNamespaceStringTokens($tokens, &$afterNamespace, &$start, &$end)
    {
        $afterNamespace = false;
        $start = 0;
        $end = 0;
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $this->shiftNamespaceOffsets($token, $afterNamespace, $start, $end);

                continue;
            }

            yield $token;
        }
    }

    protected function getNamespaceOffsets($token, &$afterNamespace, &$start, &$end)
    {
        if ($token[0] === T_NAMESPACE) {
            $afterNamespace = true;
        }
        $length = mb_strlen($token[1]);
        if (!$afterNamespace) {
            $start += $length;
        }
        $end += $length;
        if ($afterNamespace && $token[0] === T_STRING) {
            return [$token[1], $start, $end];
        }

        return false;
    }

    protected function extractNamespace($tokens)
    {
        foreach ($this->filterNamespaceStringTokens($tokens, $afterNamespace, $start, $end) as $token) {
            if ($this->getNamespaceOffsets($token, $afterNamespace, $start, $end)) {
                return [$token[1], $start, $end];
            }
        }

        return false;
    }

    protected function handleNamespace($output)
    {
        $namespace = $this->extractNamespace(array_slice(token_get_all('?>' . $output), 1));

        if ($namespace) {
            list($namespace, $start, $end) = $namespace;
            $output = "<?php\n\nnamespace $namespace;\n\n?>" .
                mb_substr($output, 0, $start) .
                ltrim(mb_substr($output, $end), ' ;');
        }

        return $output;
    }

    protected function setUpPostRender(&$options)
    {
        $postRender = isset($options['postRender']) ? $options['postRender'] : null;
        $onOutput = isset($options['on_output']) ? $options['on_output'] : null;
        $options['on_output'] = function (OutputEvent $event) use ($onOutput, $postRender) {
            if ($onOutput) {
                call_user_func($onOutput, $event);
            }
            $output = $event->getOutput();
            $pos = stripos($output, 'namespace');
            if ($pos !== false && in_array(substr($output, $pos + 9, 1), ["\n", "\r", "\t", ' '])) {
                $output = $this->handleNamespace($output);
            }
            if ($postRender) {
                $output = call_user_func($postRender, $output);
            }
            $event->setOutput($output);
        };
    }
}

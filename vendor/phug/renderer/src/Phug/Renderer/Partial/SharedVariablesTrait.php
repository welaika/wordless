<?php

namespace Phug\Renderer\Partial;

use Phug\Util\OptionInterface;

/**
 * Trait SharedVariablesTrait: require OptionInterface to be implemented.
 */
trait SharedVariablesTrait
{
    /**
     * Returns merged globals, shared variables and locals.
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function mergeWithSharedVariables(array $parameters)
    {
        /* @var OptionInterface $this */

        return array_merge(
            $this->getOption('globals'),
            $this->getOption('shared_variables'),
            $parameters
        );
    }

    /**
     * Share variables (local templates parameters) with all future templates rendered.
     *
     * @example $renderer->share('lang', 'fr')
     * @example $renderer->share(['title' => 'My blog', 'today' => new DateTime()])
     *
     * @param array|string $variables a variables name-value pairs or a single variable name
     * @param mixed        $value     the variable value if the first argument given is a string
     *
     * @return $this
     */
    public function share($variables, $value = null)
    {
        /* @var OptionInterface $this */

        if (func_num_args() === 2) {
            $key = $variables;
            $variables = [];
            $variables[$key] = $value;
        }

        return $this->setOptionsRecursive([
            'shared_variables' => $variables,
        ]);
    }

    /**
     * Remove all previously set shared variables.
     */
    public function resetSharedVariables()
    {
        /* @var OptionInterface $this */

        return $this->setOption('shared_variables', []);
    }
}

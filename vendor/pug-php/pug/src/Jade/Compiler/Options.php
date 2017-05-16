<?php

namespace Jade\Compiler;

use Jade\Jade;

/**
 * Class Jade Compiler.
 */
class Options extends ExpressionCompiler
{
    /**
     * @var bool
     */
    protected $prettyprint = false;
    /**
     * @var bool
     */
    protected $phpSingleLine = false;
    /**
     * @var bool
     */
    protected $allowMixinOverride = false;
    /**
     * @var bool
     */
    protected $keepNullAttributes = false;
    /**
     * @var bool
     */
    protected $filterAutoLoad = true;
    /**
     * @var bool
     */
    protected $terse = true;
    /**
     * @var bool
     */
    protected $restrictedScope = false;
    /**
     * @var array
     */
    protected $customKeywords = array();
    /**
     * @var array
     */
    protected $options = array();

    protected function setOptionType($option, $type)
    {
        $types = explode('|', $type);
        if (!in_array(gettype($this->$option), $types)) {
            settype($this->$option, $types[0]);
        }
    }

    /**
     * Get a jade engine reference or an options array and return needed options.
     *
     * @param array/Jade $options
     *
     * @return array
     */
    protected function setOptions($options)
    {
        $optionTypes = array(
            'prettyprint' => 'boolean',
            'phpSingleLine' => 'boolean',
            'allowMixinOverride' => 'boolean',
            'keepNullAttributes' => 'boolean',
            'filterAutoLoad' => 'boolean',
            'restrictedScope' => 'boolean',
            'indentSize' => 'integer',
            'expressionLanguage' => 'string|integer',
            'indentChar' => 'string',
            'customKeywords' => 'array',
        );

        if ($options instanceof Jade) {
            $this->jade = $options;
            $options = array();

            foreach ($optionTypes as $option => $type) {
                $this->$option = $this->jade->getOption($option);
                $options[$option] = $this->$option;
                $this->setOptionType($option, $type);
            }

            $this->quote = $this->jade->getOption('singleQuote') ? '\'' : '"';

            return $options;
        }

        foreach (array_intersect_key($optionTypes, $options) as $option => $type) {
            $this->$option = $options[$option];
            $this->setOptionType($option, $type);
        }

        $this->quote = isset($options['singleQuote']) && $options['singleQuote'] ? '\'' : '"';

        return $options;
    }

    protected function getRawOptionValue($option)
    {
        return is_null($this->jade)
            ? array_key_exists($option, $this->options)
                ? $this->options[$option]
                : null
            : $this->jade->getOption($option);
    }

    /**
     * Get an option from the jade engine if set or from the options array else.
     *
     * @param string $option
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getOption($option, $defaultValue = null)
    {
        if (is_null($this->jade) && !array_key_exists($option, $this->options) && func_num_args() < 2) {
            throw new \InvalidArgumentException("$option is not a valid option name.", 28);
        }

        $value = $this->getRawOptionValue($option);

        return is_null($value) ? $defaultValue : $value;
    }
}

<?php

namespace Jade\Engine;

/**
 * Class Jade\Engine\Options.
 */
class Options extends Keywords
{
    /**
     * @var array
     */
    protected $options = array(
        'allowMixedIndent'   => true,
        'allowMixinOverride' => true,
        'basedir'            => null,
        'cache'              => null,
        'classAttribute'     => null,
        'customKeywords'     => array(),
        'expressionLanguage' => 'auto',
        'extension'          => array('.pug', '.jade'),
        'filterAutoLoad'     => true,
        'indentChar'         => ' ',
        'indentSize'         => 2,
        'keepBaseName'       => false,
        'keepNullAttributes' => false,
        'phpSingleLine'      => false,
        'postRender'         => null,
        'preRender'          => null,
        'prettyprint'        => false,
        'restrictedScope'    => false,
        'singleQuote'        => false,
        'stream'             => null,
        'upToDateCheck'      => true,
    );

    /**
     * Get standard or custom option, return the previously setted value or the default value else.
     *
     * Throw a invalid argument exception if the option does not exists.
     *
     * @param string name
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("$name is not a valid option name.", 2);
        }

        return $this->options[$name];
    }

    /**
     * Set one standard option (listed in $this->options).
     *
     * @param string name
     * @param mixed option value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException("$name is not a valid option name.", 3);
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set multiple standard options.
     *
     * @param array option list
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Set one custom option.
     *
     * @param string name
     * @param mixed option value
     *
     * @return $this
     */
    public function setCustomOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set multiple custom options.
     *
     * @param array options list
     *
     * @return $this
     */
    public function setCustomOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }
}

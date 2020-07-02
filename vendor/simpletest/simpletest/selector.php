<?php

require_once __DIR__ . '/tag.php';
require_once __DIR__ . '/encoding.php';

interface SelectorInterface
{

}

/**
 * Used to extract form elements for testing against.
 * Searches by name attribute.
 */
class SelectByName implements SelectorInterface
{
    private $name;

    /**
     * Stashes the name for later comparison.
     *
     * @param string $name     Name attribute to match.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Accessor for name.
     *
     * @returns string $name       Name to match.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Compares with name attribute of widget.
     *
     * @param SimpleWidget $widget    Control to compare.
     */
    public function isMatch($widget)
    {
        return ($widget->getName() === $this->name);
    }
}

/**
 * Used to extract form elements for testing against.
 * Searches by visible label or alt text.
 */
class SelectByLabel implements SelectorInterface
{
    private $label;

    /**
     * Stashes the name for later comparison.
     *
     * @param string $label     Visible text to match.
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * Comparison. Compares visible text of widget or related label.
     *
     * @param SimpleWidget $widget    Control to compare.
     */
    public function isMatch($widget)
    {
        if (! method_exists($widget, 'isLabel')) {
            return false;
        }

        return $widget->isLabel($this->label);
    }
}

/**
 * Used to extract form elements for testing against.
 * Searches dy id attribute.
 */
class SelectById implements SelectorInterface
{
    private $id;

    /**
     * Stashes the name for later comparison.
     *
     * @param string $id     ID atribute to match.
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Comparison. Compares id attribute of widget.
     *
     * @param SimpleWidget $widget    Control to compare.
     */
    public function isMatch($widget)
    {
        return $widget->isId($this->id);
    }
}

/**
 * Used to extract form elements for testing against.
 * Searches by visible label, name or alt text.
 */
class SelectByLabelOrName implements SelectorInterface
{
    private $label;

    /**
     * Stashes the name/label for later comparison.
     *
     * @param string $label     Visible text to match.
     */
    public function __construct($label)
    {
        $this->label = $label;
    }

    /**
     * Comparison. Compares visible text of widget or related label or name.
     *
     * @param SimpleWidget $widget    Control to compare.
     */
    public function isMatch($widget)
    {
        if (method_exists($widget, 'isLabel')) {
            if ($widget->isLabel($this->label)) {
                return true;
            }
        }

        return ($widget->getName() == $this->label);
    }
}

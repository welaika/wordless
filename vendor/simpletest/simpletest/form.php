<?php

require_once __DIR__ . '/tag.php';
require_once __DIR__ . '/encoding.php';
require_once __DIR__ . '/selector.php';

/**
 * Form tag class to hold widget values.
 */
class SimpleForm
{
    private $action;
    private $buttons;
    private $checkboxes;
    private $default_target;
    private $encoding;
    private $id;
    private $images;
    private $method;
    private $radios;
    private $widgets;

    /**
     * Starts with no held controls/widgets.
     *
     * @param SimpleTag $tag        Form tag to read.
     * @param SimplePage $page      Holding page.
     */
    public function __construct($tag, $page)
    {
        $this->action         = $this->createAction($tag->getAttribute('action'), $page);
        $this->buttons        = array();
        $this->checkboxes     = array();
        $this->default_target = false;
        $this->encoding       = $this->setEncodingClass($tag);
        $this->id             = $tag->getAttribute('id');
        $this->images         = array();
        $this->method         = $tag->getAttribute('method');
        $this->radios         = array();
        $this->widgets        = array();
    }

    /**
     * Creates the request packet to be sent by the form.
     *
     * @param SimpleTag $tag        Form tag to read.
     *
     * @return string               Packet class.
     */
    protected function setEncodingClass($tag)
    {
        if (strtolower($tag->getAttribute('method')) === 'post') {
            if (strtolower($tag->getAttribute('enctype')) === 'multipart/form-data') {
                return 'SimpleMultipartEncoding';
            }

            return 'SimplePostEncoding';
        }

        return 'SimpleGetEncoding';
    }

    /**
     * Sets the frame target within a frameset.
     *
     * @param string $frame        Name of frame.
     */
    public function setDefaultTarget($frame)
    {
        $this->default_target = $frame;
    }

    /**
     * Accessor for method of form submission.
     *
     * @return string           Either get or post.
     */
    public function getMethod()
    {
        return ($this->method ? strtolower($this->method) : 'get');
    }

    /**
     * Combined action attribute with current location to get an absolute form target.
     *
     * @param string $action    Action attribute from form tag.
     * @param $page   Page location.
     *
     * @return SimpleUrl        Absolute form target.
     */
    protected function createAction($action, $page)
    {
        if (($action === '') || ($action === false)) {
            return $page->expandUrl($page->getUrl());
        }

        return $page->expandUrl(new SimpleUrl($action));
    }

    /**
     * Absolute URL of the target.
     *
     * @return SimpleUrl           URL target.
     */
    public function getAction()
    {
        $url = $this->action;
        if ($this->default_target && ! $url->getTarget()) {
            $url->setTarget($this->default_target);
        }
        if ($this->getMethod() === 'get') {
            $url->clearRequest();
        }

        return $url;
    }

    /**
     * Creates the encoding for the current values in the form.
     *
     * @return SimpleFormEncoding    Request to submit.
     */
    protected function encode()
    {
        $class    = $this->encoding;
        $encoding = new $class();
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            $this->widgets[$i]->write($encoding);
        }

        return $encoding;
    }

    /**
     * ID field of form for unique identification.
     *
     * @return string           Unique tag ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds a tag contents to the form.
     *
     * @param SimpleWidget $tag        Input tag to add.
     */
    public function addWidget($tag)
    {
        if (strtolower($tag->getAttribute('type')) === 'submit') {
            $this->buttons[] = $tag;
        } elseif (strtolower($tag->getAttribute('type')) === 'image') {
            $this->images[] = $tag;
        } elseif ($tag->getName()) {
            $this->setWidget($tag);
        }
    }

    /**
     * Sets the widget into the form, grouping radio buttons if any.
     *
     * @param SimpleWidget $tag   Incoming form control.
     */
    protected function setWidget($tag)
    {
        if (strtolower($tag->getAttribute('type')) === 'radio') {
            $this->addRadioButton($tag);
        } elseif (strtolower($tag->getAttribute('type')) === 'checkbox') {
            $this->addCheckbox($tag);
        } else {
            $this->widgets[] = $tag;
        }
    }

    /**
     * Adds a radio button, building a group if necessary.
     *
     * @param SimpleRadioButtonTag $tag   Incoming form control.
     */
    protected function addRadioButton($tag)
    {
        if (! isset($this->radios[$tag->getName()])) {
            $this->widgets[]               = new SimpleRadioGroup();
            $this->radios[$tag->getName()] = count($this->widgets) - 1;
        }
        $this->widgets[$this->radios[$tag->getName()]]->addWidget($tag);
    }

    /**
     * Adds a checkbox, making it a group on a repeated name.
     *
     * @param SimpleCheckboxTag $tag   Incoming form control.
     */
    protected function addCheckbox($tag)
    {
        if (! isset($this->checkboxes[$tag->getName()])) {
            $this->widgets[]                   = $tag;
            $this->checkboxes[$tag->getName()] = count($this->widgets) - 1;
        } else {
            $index = $this->checkboxes[$tag->getName()];
            if (! is_a($this->widgets[$index], 'SimpleCheckboxGroup')) {
                $previous              = $this->widgets[$index];
                $this->widgets[$index] = new SimpleCheckboxGroup();
                $this->widgets[$index]->addWidget($previous);
            }
            $this->widgets[$index]->addWidget($tag);
        }
    }

    /**
     * Extracts current value from form.
     *
     * @param $selector Criteria to apply.
     *
     * @return string/array Value(s) as string or null if not set.
     */
    public function getValue(SelectorInterface $selector)
    {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                return $this->widgets[$i]->getValue();
            }
        }
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                return $button->getValue();
            }
        }

        return;
    }

    /**
     * Sets a widget value within the form.
     *
     * @param $selector   Criteria to apply.
     * @param string $value Value to input into the widget.
     *
     * @return bool                   True if value is legal, false otherwise.
     * If the field is not present, nothing will be set.
     */
    public function setField(SelectorInterface $selector, $value, $position=false)
    {
        $success   = false;
        $_position = 0;
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                $_position++;
                if ($position === false or $_position === (int) $position) {
                    if ($this->widgets[$i]->setValue($value)) {
                        $success = true;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Used by the page object to set widgets labels to external label tags.
     *
     * @param $selector   Criteria to apply.
     */
    public function attachLabelBySelector(SelectorInterface $selector, $label)
    {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                if (method_exists($this->widgets[$i], 'setLabel')) {
                    $this->widgets[$i]->setLabel($label);

                    return;
                }
            }
        }
    }

    /**
     * Test to see if a form has a submit button.
     *
     * @param $selector Criteria to apply.
     *
     * @return bool True if present.
     */
    public function hasSubmit(SelectorInterface $selector)
    {
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test to see if a form has an image control.
     *
     * @param $selector Criteria to apply.
     *
     * @return bool True if present.
     */
    public function hasImage(SelectorInterface $selector)
    {
        foreach ($this->images as $image) {
            if ($selector->isMatch($image)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the submit values for a selected button.
     *
     * @param $selector Criteria to apply.
     * @param hash $additional Additional data for the form.
     *
     * @return SimpleEncoding Submitted values or false if there is no such button in the
     * form.
     */
    public function submitButton(SelectorInterface $selector, $additional = false)
    {
        $additional = $additional ? $additional : array();
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                $encoding = $this->encode();
                $button->write($encoding);
                if ($additional) {
                    $encoding->merge($additional);
                }

                return $encoding;
            }
        }

        return false;
    }

    /**
     * Gets the submit values for an image.
     *
     * @param $selector Criteria to apply.
     * @param int $x X-coordinate of click.
     * @param int $y Y-coordinate of click.
     * @param hash $additional  Additional data for the form.
     *
     * @return SimpleEncoding Submitted values or false if there is no such button in the form.
     */
    public function submitImage(SelectorInterface $selector, $x, $y, $additional = false)
    {
        $additional = $additional ? $additional : array();
        foreach ($this->images as $image) {
            if ($selector->isMatch($image)) {
                $encoding = $this->encode();
                $image->write($encoding, $x, $y);
                if ($additional) {
                    $encoding->merge($additional);
                }

                return $encoding;
            }
        }

        return false;
    }

    /**
     * Simply submits the form without the submit button value.
     * Used when there is only one button or it is unimportant.
     *
     * @return hash           Submitted values.
     */
    public function submit($additional = false)
    {
        $encoding = $this->encode();
        if ($additional) {
            $encoding->merge($additional);
        }

        return $encoding;
    }
}

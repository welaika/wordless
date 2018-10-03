<?php

namespace Phug\Lexer\Partial;

use Phug\Lexer;

trait IndentStyleTrait
{
    /**
     * Contains the currently detected indentation style.
     *
     * @var string
     */
    private $indentStyle;

    /**
     * Contains the currently detected indentation width.
     *
     * @var int
     */
    private $indentWidth;

    /**
     * Returns the currently used indentation style.
     *
     * @return string
     */
    public function getIndentStyle()
    {
        return $this->indentStyle;
    }

    /**
     * Sets the current indentation style to a new one.
     *
     * The value needs to be one of the `Lexer::INDENT_*` constants, but you can also just
     * pass either a single space or a single tab for the respective style.
     *
     * @param $indentStyle
     *
     * @return $this
     */
    public function setIndentStyle($indentStyle)
    {
        if (!in_array($indentStyle, [null, Lexer::INDENT_TAB, Lexer::INDENT_SPACE])) {
            throw new \InvalidArgumentException(
                'indentStyle needs to be null or one of the INDENT_* constants of the lexer'
            );
        }

        $this->indentStyle = $indentStyle;

        return $this;
    }

    /**
     * Returns the currently used indentation width.
     *
     * @return int
     */
    public function getIndentWidth()
    {
        return $this->indentWidth;
    }

    /**
     * Sets the currently used indentation width.
     *
     * The value of this specifies if e.g. 2 spaces make up one indentation level or 4.
     *
     * @param $indentWidth
     *
     * @return $this
     */
    public function setIndentWidth($indentWidth)
    {
        if (!is_null($indentWidth) &&
            (!is_int($indentWidth) || $indentWidth < 1)
        ) {
            throw new \InvalidArgumentException(
                'indentWidth needs to be null or an integer above 0'
            );
        }

        $this->indentWidth = $indentWidth;

        return $this;
    }
}

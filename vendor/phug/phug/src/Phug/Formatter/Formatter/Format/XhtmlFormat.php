<?php

namespace Phug\Formatter\Format;

use Phug\Formatter;
use Phug\Formatter\MarkupInterface;

abstract class XhtmlFormat extends XmlFormat
{
    const DOCTYPE = '<!DOCTYPE %s PUBLIC "%s" "%s">';
    const DOCTYPE_DTD = '';
    const DOCTYPE_DTD_URL = '';
    const DOCTYPE_LANGUAGE = 'html';
    const SELF_CLOSING_TAG = '<%s />';

    public function __construct(Formatter $formatter = null)
    {
        $this->setOptionsRecursive([
            'white_space_sensitive_tags' => [
                'pre',
                'textarea',
            ],
            'inline_tags' => [
                'a',
                'abbr',
                'acronym',
                'b',
                'br',
                'code',
                'command',
                'embed',
                'em',
                'font',
                'i',
                'input',
                'img',
                'ins',
                'kbd',
                'keygen',
                'map',
                'samp',
                'small',
                'span',
                'strong',
                'sub',
                'sup',
            ],
            'self_closing_tags' => [
                'area',
                'base',
                'br',
                'col',
                'command',
                'embed',
                'hr',
                'img',
                'input',
                'keygen',
                'link',
                'meta',
                'param',
                'source',
                'track',
                'wbr',
            ],
        ]);

        parent::__construct($formatter);

        $this->addPatterns([
            'doctype_language' => static::DOCTYPE_LANGUAGE,
            'doctype_dtd'      => static::DOCTYPE_DTD,
            'doctype_dtd_url'  => static::DOCTYPE_DTD_URL,
        ]);

        $this->setPattern('doctype', $this->pattern(
            'doctype',
            $this->pattern('doctype_language'),
            $this->pattern('doctype_dtd'),
            $this->pattern('doctype_dtd_url')
        ));
    }

    public function isSelfClosingTag(MarkupInterface $element, $isSelfClosing = false)
    {
        return parent::isSelfClosingTag(
            $element,
            $isSelfClosing || $element->isAutoClosed() || $element->belongsTo(
                $this->getOption('self_closing_tags')
            )
        );
    }

    protected function isBlockTag(MarkupInterface $element)
    {
        if ($element->belongsTo($this->getOption('inline_tags'))) {
            return false;
        }

        if ($element->hasParent()) {
            for ($element = $element->getParent(); $element->hasParent(); $element = $element->getParent()) {
                if ($element instanceof MarkupInterface) {
                    if ($this->isWhiteSpaceSensitive($element)) {
                        return false;
                    }

                    return $this->isBlockTag($element);
                }
            }
        }

        return true;
    }

    public function isWhiteSpaceSensitive(MarkupInterface $element)
    {
        return $element->belongsTo($this->getOption('white_space_sensitive_tags'));
    }
}

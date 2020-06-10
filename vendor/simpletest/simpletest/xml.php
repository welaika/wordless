<?php

require_once __DIR__ . '/scorer.php';

/**
 * Creates the XML needed for remote communication by SimpleTest.
 */
class XmlReporter extends SimpleReporter
{
    private $indent;
    private $namespace;

    /**
     * Sets up indentation and namespace.
     *
     * @param string $namespace        Namespace to add to each tag.
     * @param string $indent           Indenting to add on each nesting.
     */
    public function __construct($namespace = false, $indent = '  ')
    {
        parent::__construct();
        $this->namespace = ($namespace ? $namespace . ':' : '');
        $this->indent    = $indent;
    }

    /**
     * Calculates the pretty printing indent level from the current level of nesting.
     *
     * @param int $offset  Extra indenting level.
     *
     * @return string          Leading space.
     */
    protected function getIndent($offset = 0)
    {
        return str_repeat(
                $this->indent,
                count($this->getTestList()) + $offset);
    }

    /**
     * Converts character string to parsed XML entities string.
     *
     * @param string text        Unparsed character data.
     *
     * @return string            Parsed character data.
     */
    public function toParsedXml($text)
    {
        return str_replace(
                array('&', '<', '>', '"', '\''),
                array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;'),
                $text);
    }

    /**
     * Paints the start of a group test.
     *
     * @param string $test_name   Name of test that is starting.
     * @param int $size       Number of test cases starting.
     */
    public function paintGroupStart($test_name, $size)
    {
        parent::paintGroupStart($test_name, $size);
        print $this->getIndent();
        print '<' . $this->namespace . "group size=\"$size\">\n";
        print $this->getIndent(1);
        print '<' . $this->namespace . 'name>' .
                $this->toParsedXml($test_name) .
                '</' . $this->namespace . "name>\n";
    }

    /**
     * Paints the end of a group test.
     *
     * @param string $test_name   Name of test that is ending.
     */
    public function paintGroupEnd($test_name)
    {
        print $this->getIndent();
        print '</' . $this->namespace . "group>\n";
        parent::paintGroupEnd($test_name);
    }

    /**
     * Paints the start of a test case.
     *
     * @param string $test_name   Name of test that is starting.
     */
    public function paintCaseStart($test_name)
    {
        parent::paintCaseStart($test_name);
        print $this->getIndent();
        print '<' . $this->namespace . "case>\n";
        print $this->getIndent(1);
        print '<' . $this->namespace . 'name>' .
                $this->toParsedXml($test_name) .
                '</' . $this->namespace . "name>\n";
    }

    /**
     * Paints the end of a test case.
     *
     * @param string $test_name   Name of test that is ending.
     */
    public function paintCaseEnd($test_name)
    {
        print $this->getIndent();
        print '</' . $this->namespace . "case>\n";
        parent::paintCaseEnd($test_name);
    }

    /**
     * Paints the start of a test method.
     *
     * @param string $test_name   Name of test that is starting.
     */
    public function paintMethodStart($test_name)
    {
        parent::paintMethodStart($test_name);
        print $this->getIndent();
        print '<' . $this->namespace . "test>\n";
        print $this->getIndent(1);
        print '<' . $this->namespace . 'name>' .
                $this->toParsedXml($test_name) .
                '</' . $this->namespace . "name>\n";
    }

    /**
     * Paints the end of a test method.
     *
     * @param string $test_name   Name of test that is ending.
     * @param int $progress   Number of test cases ending.
     */
    public function paintMethodEnd($test_name)
    {
        print $this->getIndent();
        print '</' . $this->namespace . "test>\n";
        parent::paintMethodEnd($test_name);
    }

    /**
     * Paints pass as XML.
     *
     * @param string $message        Message to encode.
     */
    public function paintPass($message)
    {
        parent::paintPass($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'pass>';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "pass>\n";
    }

    /**
     * Paints failure as XML.
     *
     * @param string $message        Message to encode.
     */
    public function paintFail($message)
    {
        parent::paintFail($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'fail>';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "fail>\n";
    }

    /**
     * Paints error as XML.
     *
     * @param string $message        Message to encode.
     */
    public function paintError($message)
    {
        parent::paintError($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'exception>';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "exception>\n";
    }

    /**
     * Paints exception as XML.
     *
     * @param Exception $exception    Exception to encode.
     */
    public function paintException($exception)
    {
        parent::paintException($exception);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'exception>';
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message [' . $exception->getMessage() .
                '] in [' . $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "exception>\n";
    }

    /**
     * Paints the skipping message and tag.
     *
     * @param string $message        Text to display in skip tag.
     */
    public function paintSkip($message)
    {
        parent::paintSkip($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'skip>';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "skip>\n";
    }

    /**
     * Paints a simple supplementary message.
     *
     * @param string $message        Text to display.
     */
    public function paintMessage($message)
    {
        parent::paintMessage($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'message>';
        print $this->toParsedXml($message);
        print '</' . $this->namespace . "message>\n";
    }

    /**
     * Paints a formatted ASCII message such as a privateiable dump.
     *
     * @param string $message        Text to display.
     */
    public function paintFormattedMessage($message)
    {
        parent::paintFormattedMessage($message);
        print $this->getIndent(1);
        print '<' . $this->namespace . 'formatted>';
        print "<![CDATA[$message]]>";
        print '</' . $this->namespace . "formatted>\n";
    }

    /**
     * Serialises the event object.
     *
     * @param string $type        Event type as text.
     * @param mixed $payload      Message or object.
     */
    public function paintSignal($type, $payload)
    {
        parent::paintSignal($type, $payload);
        print $this->getIndent(1);
        print '<' . $this->namespace . "signal type=\"$type\">";
        print '<![CDATA[' . serialize($payload) . ']]>';
        print '</' . $this->namespace . "signal>\n";
    }

    /**
     * Paints the test document header.
     *
     * @param string $test_name     First test top level to start.
     *
     * @abstract
     */
    public function paintHeader($test_name)
    {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/xml');
        }
        print '<?xml version="1.0"';
        if ($this->namespace) {
            print ' xmlns:' . $this->namespace .
                    '="www.lastcraft.com/SimpleTest/Beta3/Report"';
        }
        print "?>\n";
        print '<' . $this->namespace . "run>\n";
    }

    /**
     * Paints the test document footer.
     *
     * @param string $test_name        The top level test.
     *
     * @abstract
     */
    public function paintFooter($test_name)
    {
        print '</' . $this->namespace . "run>\n";
    }
}

/**
 * Accumulator for incoming tag.
 * Holds the incoming test structure information for later dispatch to the reporter.
 */
class NestedXmlTag
{
    private $attributes;
    private $name;

    /**
     * Sets the basic test information except the name.
     *
     * @param hash $attributes   Name value pairs.
     */
    public function __construct($attributes)
    {
        $this->name       = false;
        $this->attributes = $attributes;
    }

    /**
     * Sets the test case/method name.
     *
     * @param string $name        Name of test.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Accessor for name.
     *
     * @return string        Name of test.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Accessor for attributes.
     *
     * @return hash        All attributes.
     */
    protected function getAttributes()
    {
        return $this->attributes;
    }
}

/**
 * Accumulator for incoming method tag.
 * Holds the incoming test structure information for later dispatch to the reporter.
 */
class NestedMethodTag extends NestedXmlTag
{
    /**
     * Sets the basic test information except the name.
     *
     * @param hash $attributes   Name value pairs.
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    /**
     * Signals the appropriate start event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintStart(&$listener)
    {
        $listener->paintMethodStart($this->getName());
    }

    /**
     * Signals the appropriate end event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintEnd(&$listener)
    {
        $listener->paintMethodEnd($this->getName());
    }
}

/**
 * Accumulator for incoming case tag.
 * Holds the incoming test structure information for later dispatch to the reporter.
 */
class NestedCaseTag extends NestedXmlTag
{
    /**
     * Sets the basic test information except the name.
     *
     * @param hash $attributes   Name value pairs.
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    /**
     * Signals the appropriate start event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintStart(&$listener)
    {
        $listener->paintCaseStart($this->getName());
    }

    /**
     * Signals the appropriate end event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintEnd(&$listener)
    {
        $listener->paintCaseEnd($this->getName());
    }
}

/**
 * Accumulator for incoming group tag.
 * Holds the incoming test structure information for later dispatch to the reporter.
 */
class NestedGroupTag extends NestedXmlTag
{
    /**
     * Sets the basic test information except the name.
     *
     * @param hash $attributes   Name value pairs.
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    /**
     * Signals the appropriate start event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintStart(&$listener)
    {
        $listener->paintGroupStart($this->getName(), $this->getSize());
    }

    /**
     * Signals the appropriate end event on the listener.
     *
     * @param SimpleReporter $listener    Target for events.
     */
    public function paintEnd(&$listener)
    {
        $listener->paintGroupEnd($this->getName());
    }

    /**
     * The size in the attributes.
     *
     * @return int     Value of size attribute or zero.
     */
    public function getSize()
    {
        $attributes = $this->getAttributes();
        if (isset($attributes['SIZE'])) {
            return (integer) $attributes['SIZE'];
        }

        return 0;
    }
}

/**
 * Parser for importing the output of the XmlReporter.
 * Dispatches that output to another reporter.
 */
class SimpleTestXmlParser
{
    private $attributes;
    private $content;
    private $expat;
    private $in_content_tag;
    private $listener;
    private $tag_stack;

    /**
     * Loads a listener with the SimpleReporter interface.
     *
     * @param SimpleReporter $listener   Listener of tag events.
     */
    public function __construct(&$listener)
    {
        $this->attributes     = array();
        $this->content        = '';
        $this->expat          = $this->createParser();
        $this->in_content_tag = false;
        $this->listener       = $listener;
        $this->tag_stack      = array();
    }

    /**
     * Parses a block of XML sending the results to the listener.
     *
     * @param string $chunk        Block of text to read.
     *
     * @return bool             True if valid XML.
     */
    public function parse($chunk)
    {
        if (! xml_parse($this->expat, $chunk)) {
            $errormessage = sprintf(
                "XML parse error %d '%s' at line %d, column %d (byte %d).",
                xml_get_error_code($this->expat),
                xml_error_string(xml_get_error_code($this->expat)),
                xml_get_current_line_number($this->expat),
                xml_get_current_column_number($this->expat),
                xml_get_current_byte_index($this->expat)
            );
            trigger_error($errormessage);

            return false;
        }

        return true;
    }

    /**
     * Sets up expat as the XML parser.
     *
     * @return resource        Expat handle.
     */
    protected function createParser()
    {
        $expat = xml_parser_create();
        xml_set_object($expat, $this);
        xml_set_element_handler($expat, 'startElement', 'endElement');
        xml_set_character_data_handler($expat, 'addContent');
        xml_set_default_handler($expat, 'defaultContent');

        return $expat;
    }

    /**
     * Opens a new test nesting level.
     *
     * @return NestedXmlTag     The group, case or method tag to start.
     */
    protected function pushNestingTag($nested)
    {
        array_unshift($this->tag_stack, $nested);
    }

    /**
     * Accessor for current test structure tag.
     *
     * @return NestedXmlTag     The group, case or method tag being parsed.
     */
    protected function getCurrentNestingTag()
    {
        return $this->tag_stack[0];
    }

    /**
     * Ends a nesting tag.
     *
     * @return NestedXmlTag     The group, case or method tag just finished.
     */
    protected function popNestingTag()
    {
        return array_shift($this->tag_stack);
    }

    /**
     * Test if tag is a leaf node with only text content.
     *
     * @param string $tag        XML tag name.
     *
     * @return @boolean          True if leaf, false if nesting.
     *
     * @private
     */
    protected function isLeaf($tag)
    {
        return in_array($tag, array(
                'NAME', 'PASS', 'FAIL', 'EXCEPTION', 'SKIP', 'MESSAGE', 'FORMATTED', 'SIGNAL'));
    }

    /**
     * Handler for start of event element.
     *
     * @param resource $expat     Parser handle.
     * @param string $tag         Element name.
     * @param hash $attributes    Name value pairs. Attributes without content are marked as true.
     */
    protected function startElement($expat, $tag, $attributes)
    {
        $this->attributes = $attributes;
        if ($tag === 'GROUP') {
            $this->pushNestingTag(new NestedGroupTag($attributes));
        } elseif ($tag === 'CASE') {
            $this->pushNestingTag(new NestedCaseTag($attributes));
        } elseif ($tag === 'TEST') {
            $this->pushNestingTag(new NestedMethodTag($attributes));
        } elseif ($this->isLeaf($tag)) {
            $this->in_content_tag = true;
            $this->content        = '';
        }
    }

    /**
     * End of element event.
     *
     * @param resource $expat     Parser handle.
     * @param string $tag         Element name.
     */
    protected function endElement($expat, $tag)
    {
        $this->in_content_tag = false;
        if (in_array($tag, array('GROUP', 'CASE', 'TEST'))) {
            $nesting_tag = $this->popNestingTag();
            $nesting_tag->paintEnd($this->listener);
        } elseif ($tag === 'NAME') {
            $nesting_tag = $this->getCurrentNestingTag();
            $nesting_tag->setName($this->content);
            $nesting_tag->paintStart($this->listener);
        } elseif ($tag === 'PASS') {
            $this->listener->paintPass($this->content);
        } elseif ($tag === 'FAIL') {
            $this->listener->paintFail($this->content);
        } elseif ($tag === 'EXCEPTION') {
            $this->listener->paintError($this->content);
        } elseif ($tag === 'SKIP') {
            $this->listener->paintSkip($this->content);
        } elseif ($tag === 'SIGNAL') {
            $this->listener->paintSignal(
                    $this->attributes['TYPE'],
                    unserialize($this->content));
        } elseif ($tag === 'MESSAGE') {
            $this->listener->paintMessage($this->content);
        } elseif ($tag === 'FORMATTED') {
            $this->listener->paintFormattedMessage($this->content);
        }
    }

    /**
     * Content between start and end elements.
     *
     * @param resource $expat     Parser handle.
     * @param string $text        Usually output messages.
     */
    protected function addContent($expat, $text)
    {
        if ($this->in_content_tag) {
            $this->content .= $text;
        }

        return true;
    }

    /**
     * XML and Doctype handler. Discards all such content.
     *
     * @param resource $expat     Parser handle.
     * @param string $default     Text of default content.
     */
    protected function defaultContent($expat, $default)
    {
    }
}

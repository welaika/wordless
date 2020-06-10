<?php

require_once __DIR__ . '/browser.php';
require_once __DIR__ . '/xml.php';
require_once __DIR__ . '/test_case.php';

/**
 * Runs an XML formated test on a remote server.
 */
class RemoteTestCase
{
    private $url;
    private $dry_url;
    private $size;

    /**
     * Sets the location of the remote test.
     *
     * @param string $url       Test location.
     * @param string $dry_url   Location for dry run.
     */
    public function __construct($url, $dry_url = false)
    {
        $this->url     = $url;
        $this->dry_url = $dry_url ? $dry_url : $url;
        $this->size    = false;
    }

    /**
     * Accessor for the test name for subclasses.
     *
     * @return string           Name of the test.
     */
    public function getLabel()
    {
        return $this->url;
    }

    /**
     * Runs the top level test for this class.
     * Currently reads the data as a single chunk.
     *
     * @todo  I'll fix this once I have added iteration to the browser.
     *
     * @param SimpleReporter $reporter    Target of test results.
     *
     * @returns boolean                   True if no failures.
     */
    public function run($reporter)
    {
        $browser = $this->createBrowser();
        $xml     = $browser->get($this->url);
        if (! $xml) {
            trigger_error('Cannot read remote test URL [' . $this->url . ']');

            return false;
        }
        $parser = $this->createParser($reporter);
        if (! $parser->parse($xml)) {
            trigger_error('Cannot parse incoming XML from [' . $this->url . ']');

            return false;
        }

        return true;
    }

    /**
     * Creates a new web browser object for fetching the XML report.
     *
     * @return SimpleBrowser           New browser.
     */
    protected function createBrowser()
    {
        return new SimpleBrowser();
    }

    /**
     * Creates the XML parser.
     *
     * @param SimpleReporter $reporter    Target of test results.
     *
     * @return SimpleTestXmlListener      XML reader.
     */
    protected function createParser($reporter)
    {
        return new SimpleTestXmlParser($reporter);
    }

    /**
     * Accessor for the number of subtests.
     *
     * @return int           Number of test cases.
     */
    public function getSize()
    {
        if ($this->size === false) {
            $browser = $this->createBrowser();
            $xml     = $browser->get($this->dry_url);
            if (! $xml) {
                trigger_error('Cannot read remote test URL [' . $this->dry_url . ']');

                return false;
            }
            $reporter = new SimpleReporter();
            $parser   = $this->createParser($reporter);
            if (! $parser->parse($xml)) {
                trigger_error('Cannot parse incoming XML from [' . $this->dry_url . ']');

                return false;
            }
            $this->size = $reporter->getTestCaseCount();
        }

        return $this->size;
    }
}

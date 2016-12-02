<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../expectation.php');
require_once(dirname(__FILE__) . '/../http.php');
require_once(dirname(__FILE__) . '/../page.php');
Mock::generate('SimpleHttpHeaders');
Mock::generate('SimpleHttpResponse');

class TestOfPageInterface extends UnitTestCase
{
    public function testInterfaceOnEmptyPage()
    {
        $page = new SimplePage();
        $this->assertEqual($page->getTransportError(), 'No page fetched yet');
        $this->assertIdentical($page->getRaw(), false);
        $this->assertIdentical($page->getHeaders(), false);
        $this->assertIdentical($page->getMimeType(), false);
        $this->assertIdentical($page->getResponseCode(), false);
        $this->assertIdentical($page->getAuthentication(), false);
        $this->assertIdentical($page->getRealm(), false);
        $this->assertFalse($page->hasFrames());
        $this->assertIdentical($page->getUrls(), array());
        $this->assertIdentical($page->getTitle(), false);
    }
}

class TestOfPageHeaders extends UnitTestCase
{
    public function testUrlAccessor()
    {
        $headers = new MockSimpleHttpHeaders();

        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getHeaders', $headers);
        $response->setReturnValue('getMethod', 'POST');
        $response->setReturnValue('getUrl', new SimpleUrl('here'));
        $response->setReturnValue('getRequestData', array('a' => 'A'));

        $page = new SimplePage($response);
        $this->assertEqual($page->getMethod(), 'POST');
        $this->assertEqual($page->getUrl(), new SimpleUrl('here'));
        $this->assertEqual($page->getRequestData(), array('a' => 'A'));
    }

    public function testTransportError()
    {
        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getError', 'Ouch');

        $page = new SimplePage($response);
        $this->assertEqual($page->getTransportError(), 'Ouch');
    }

    public function testHeadersAccessor()
    {
        $headers = new MockSimpleHttpHeaders();
        $headers->setReturnValue('getRaw', 'My: Headers');

        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getHeaders', $headers);

        $page = new SimplePage($response);
        $this->assertEqual($page->getHeaders(), 'My: Headers');
    }

    public function testMimeAccessor()
    {
        $headers = new MockSimpleHttpHeaders();
        $headers->setReturnValue('getMimeType', 'text/html');

        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getHeaders', $headers);

        $page = new SimplePage($response);
        $this->assertEqual($page->getMimeType(), 'text/html');
    }

    public function testResponseAccessor()
    {
        $headers = new MockSimpleHttpHeaders();
        $headers->setReturnValue('getResponseCode', 301);

        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getHeaders', $headers);

        $page = new SimplePage($response);
        $this->assertIdentical($page->getResponseCode(), 301);
    }

    public function testAuthenticationAccessors()
    {
        $headers = new MockSimpleHttpHeaders();
        $headers->setReturnValue('getAuthentication', 'Basic');
        $headers->setReturnValue('getRealm', 'Secret stuff');

        $response = new MockSimpleHttpResponse();
        $response->setReturnValue('getHeaders', $headers);

        $page = new SimplePage($response);
        $this->assertEqual($page->getAuthentication(), 'Basic');
        $this->assertEqual($page->getRealm(), 'Secret stuff');
    }
}

class TestOfHtmlStrippingAndNormalisation extends UnitTestCase
{
    public function testImageSuppressionWhileKeepingParagraphsAndAltText()
    {
        $this->assertEqual(
                SimplePage::normalise('<img src="foo.png" /><p>some text</p><img src="bar.png" alt="bar" />'),
                'some text bar');
    }

    public function testSpaceNormalisation()
    {
        $this->assertEqual(
                SimplePage::normalise("\nOne\tTwo   \nThree\t"),
                'One Two Three');
    }

    public function testMultilinesCommentSuppression()
    {
        $this->assertEqual(
                SimplePage::normalise('<!--\n Hello \n-->'),
                '');
    }

    public function testCommentSuppression()
    {
        $this->assertEqual(
                SimplePage::normalise('<!--Hello-->'),
                '');
    }

    public function testJavascriptSuppression()
    {
        $this->assertEqual(
                SimplePage::normalise('<script attribute="test">\nHello\n</script>'),
                '');
        $this->assertEqual(
                SimplePage::normalise('<script attribute="test">Hello</script>'),
                '');
        $this->assertEqual(
                SimplePage::normalise('<script>Hello</script>'),
                '');
    }

    public function testTagSuppression()
    {
        $this->assertEqual(
                SimplePage::normalise('<b>Hello</b>'),
                'Hello');
    }

    public function testAdjoiningTagSuppression()
    {
        $this->assertEqual(
                SimplePage::normalise('<b>Hello</b><em>Goodbye</em>'),
                'HelloGoodbye');
    }

    public function testExtractImageAltTextWithDifferentQuotes()
    {
        $this->assertEqual(
                SimplePage::normalise('<img alt="One"><img alt=\'Two\'><img alt=Three>'),
                'One Two Three');
    }

    public function testExtractImageAltTextMultipleTimes()
    {
        $this->assertEqual(
                SimplePage::normalise('<img alt="One"><img alt="Two"><img alt="Three">'),
                'One Two Three');
    }

    public function testHtmlEntityTranslation()
    {
        $this->assertEqual(
                SimplePage::normalise('&lt;&gt;&quot;&amp;&#039;'),
                '<>"&\'');
    }
}

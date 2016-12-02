<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../url.php');

class TestOfUrl extends UnitTestCase
{
    public function testDefaultUrl()
    {
        $url = new SimpleUrl('');
        $this->assertEqual($url->getScheme(), '');
        $this->assertEqual($url->getHost(), '');
        $this->assertEqual($url->getScheme('http'), 'http');
        $this->assertEqual($url->getHost('localhost'), 'localhost');
        $this->assertEqual($url->getPath(), '');
    }
    
    public function testBasicParsing()
    {
        $url = new SimpleUrl('https://www.lastcraft.com/test/');
        $this->assertEqual($url->getScheme(), 'https');
        $this->assertEqual($url->getHost(), 'www.lastcraft.com');
        $this->assertEqual($url->getPath(), '/test/');
    }
    
    public function testRelativeUrls()
    {
        $url = new SimpleUrl('../somewhere.php');
        $this->assertEqual($url->getScheme(), false);
        $this->assertEqual($url->getHost(), false);
        $this->assertEqual($url->getPath(), '../somewhere.php');
    }
    
    public function testParseBareParameter()
    {
        $url = new SimpleUrl('?a');
        $this->assertEqual($url->getPath(), '');
        $this->assertEqual($url->getEncodedRequest(), '?a');
        $url->addRequestParameter('x', 'X');
        $this->assertEqual($url->getEncodedRequest(), '?a=&x=X');
    }
    
    public function testParseEmptyParameter()
    {
        $url = new SimpleUrl('?a=');
        $this->assertEqual($url->getPath(), '');
        $this->assertEqual($url->getEncodedRequest(), '?a=');
        $url->addRequestParameter('x', 'X');
        $this->assertEqual($url->getEncodedRequest(), '?a=&x=X');
    }
    
    public function testParseParameterPair()
    {
        $url = new SimpleUrl('?a=A');
        $this->assertEqual($url->getPath(), '');
        $this->assertEqual($url->getEncodedRequest(), '?a=A');
        $url->addRequestParameter('x', 'X');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&x=X');
    }
    
    public function testParseMultipleParameters()
    {
        $url = new SimpleUrl('?a=A&b=B');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=B');
        $url->addRequestParameter('x', 'X');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=B&x=X');
    }
    
    public function testParsingParameterMixture()
    {
        $url = new SimpleUrl('?a=A&b=&c');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=&c');
        $url->addRequestParameter('x', 'X');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=&c=&x=X');
    }
    
    public function testAddParametersFromScratch()
    {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', 'A');
        $this->assertEqual($url->getEncodedRequest(), '?a=A');
        $url->addRequestParameter('b', 'B');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=B');
        $url->addRequestParameter('a', 'aaa');
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=B&a=aaa');
    }
    
    public function testClearingParameters()
    {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', 'A');
        $url->clearRequest();
        $this->assertIdentical($url->getEncodedRequest(), '');
    }
    
    public function testEncodingParameters()
    {
        $url = new SimpleUrl('');
        $url->addRequestParameter('a', '?!"\'#~@[]{}:;<>,./|$%^&*()_+-=');
        $this->assertIdentical(
                $request = $url->getEncodedRequest(),
                '?a=%3F%21%22%27%23%7E%40%5B%5D%7B%7D%3A%3B%3C%3E%2C.%2F%7C%24%25%5E%26%2A%28%29_%2B-%3D');
    }
    
    public function testDecodingParameters()
    {
        $url = new SimpleUrl('?a=%3F%21%22%27%23%7E%40%5B%5D%7B%7D%3A%3B%3C%3E%2C.%2F%7C%24%25%5E%26%2A%28%29_%2B-%3D');
        $this->assertEqual(
                $url->getEncodedRequest(),
                '?a=' . urlencode('?!"\'#~@[]{}:;<>,./|$%^&*()_+-='));
    }
    
    public function testUrlInQueryDoesNotConfuseParsing()
    {
        $url = new SimpleUrl('wibble/login.php?url=http://www.google.com/moo/');
        $this->assertFalse($url->getScheme());
        $this->assertFalse($url->getHost());
        $this->assertEqual($url->getPath(), 'wibble/login.php');
        $this->assertEqual($url->getEncodedRequest(), '?url=http://www.google.com/moo/');
    }
    
    public function testSettingCordinates()
    {
        $url = new SimpleUrl('');
        $url->setCoordinates('32', '45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
        $this->assertEqual($url->getEncodedRequest(), '');
    }
    
    public function testParseCordinates()
    {
        $url = new SimpleUrl('?32,45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
    }
    
    public function testClearingCordinates()
    {
        $url = new SimpleUrl('?32,45');
        $url->setCoordinates();
        $this->assertIdentical($url->getX(), false);
        $this->assertIdentical($url->getY(), false);
    }
    
    public function testParsingParameterCordinateMixture()
    {
        $url = new SimpleUrl('?a=A&b=&c?32,45');
        $this->assertIdentical($url->getX(), 32);
        $this->assertIdentical($url->getY(), 45);
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=&c');
    }
    
    public function testParsingParameterWithBadCordinates()
    {
        $url = new SimpleUrl('?a=A&b=&c?32');
        $this->assertIdentical($url->getX(), false);
        $this->assertIdentical($url->getY(), false);
        $this->assertEqual($url->getEncodedRequest(), '?a=A&b=&c?32');
    }
    
    public function testPageSplitting()
    {
        $url = new SimpleUrl('./here/../there/somewhere.php');
        $this->assertEqual($url->getPath(), './here/../there/somewhere.php');
        $this->assertEqual($url->getPage(), 'somewhere.php');
        $this->assertEqual($url->getBasePath(), './here/../there/');
    }
    
    public function testAbsolutePathPageSplitting()
    {
        $url = new SimpleUrl("http://host.com/here/there/somewhere.php");
        $this->assertEqual($url->getPath(), "/here/there/somewhere.php");
        $this->assertEqual($url->getPage(), "somewhere.php");
        $this->assertEqual($url->getBasePath(), "/here/there/");
    }
    
    public function testSplittingUrlWithNoPageGivesEmptyPage()
    {
        $url = new SimpleUrl('/here/there/');
        $this->assertEqual($url->getPath(), '/here/there/');
        $this->assertEqual($url->getPage(), '');
        $this->assertEqual($url->getBasePath(), '/here/there/');
    }
    
    public function testPathNormalisation()
    {
        $url = new SimpleUrl();
        $this->assertEqual(
                $url->normalisePath('https://host.com/I/am/here/../there/somewhere.php'),
                'https://host.com/I/am/there/somewhere.php');
    }

    // regression test for #1535407
    public function testPathNormalisationWithSinglePeriod()
    {
        $url = new SimpleUrl();
        $this->assertEqual(
            $url->normalisePath('https://host.com/I/am/here/./../there/somewhere.php'),
            'https://host.com/I/am/there/somewhere.php');
    }
    
    // regression test for #1852413
    public function testHostnameExtractedFromUContainingAtSign()
    {
        $url = new SimpleUrl("http://localhost/name@example.com");
        $this->assertEqual($url->getScheme(), "http");
        $this->assertEqual($url->getUsername(), "");
        $this->assertEqual($url->getPassword(), "");
        $this->assertEqual($url->getHost(), "localhost");
        $this->assertEqual($url->getPath(), "/name@example.com");
    }

    public function testHostnameInLocalhost()
    {
        $url = new SimpleUrl("http://localhost/name/example.com");
        $this->assertEqual($url->getScheme(), "http");
        $this->assertEqual($url->getUsername(), "");
        $this->assertEqual($url->getPassword(), "");
        $this->assertEqual($url->getHost(), "localhost");
        $this->assertEqual($url->getPath(), "/name/example.com");
    }

    public function testUsernameAndPasswordAreUrlDecoded()
    {
        $url = new SimpleUrl('http://' . urlencode('test@test') .
                ':' . urlencode('$!�@*&%') . '@www.lastcraft.com');
        $this->assertEqual($url->getUsername(), 'test@test');
        $this->assertEqual($url->getPassword(), '$!�@*&%');
    }
    
    public function testBlitz()
    {
        $this->assertUrl(
                "https://username:password@www.somewhere.com:243/this/that/here.php?a=1&b=2#anchor",
                array("https", "username", "password", "www.somewhere.com", 243, "/this/that/here.php", "com", "?a=1&b=2", "anchor"),
                array("a" => "1", "b" => "2"));
        $this->assertUrl(
                "username:password@www.somewhere.com/this/that/here.php?a=1",
                array(false, "username", "password", "www.somewhere.com", false, "/this/that/here.php", "com", "?a=1", false),
                array("a" => "1"));
        $this->assertUrl(
                "username:password@somewhere.com:243?1,2",
                array(false, "username", "password", "somewhere.com", 243, "/", "com", "", false),
                array(),
                array(1, 2));
        $this->assertUrl(
                "https://www.somewhere.com",
                array("https", false, false, "www.somewhere.com", false, "/", "com", "", false));
        $this->assertUrl(
                "username@www.somewhere.com:243#anchor",
                array(false, "username", false, "www.somewhere.com", 243, "/", "com", "", "anchor"));
        $this->assertUrl(
                "/this/that/here.php?a=1&b=2?3,4",
                array(false, false, false, false, false, "/this/that/here.php", false, "?a=1&b=2", false),
                array("a" => "1", "b" => "2"),
                array(3, 4));
        $this->assertUrl(
                "username@/here.php?a=1&b=2",
                array(false, "username", false, false, false, "/here.php", false, "?a=1&b=2", false),
                array("a" => "1", "b" => "2"));
    }
    
    public function testAmbiguousHosts()
    {
        $this->assertUrl(
                "tigger",
                array(false, false, false, false, false, "tigger", false, "", false));
        $this->assertUrl(
                "/tigger",
                array(false, false, false, false, false, "/tigger", false, "", false));
        $this->assertUrl(
                "//tigger",
                array(false, false, false, "tigger", false, "/", false, "", false));
        $this->assertUrl(
                "//tigger/",
                array(false, false, false, "tigger", false, "/", false, "", false));
        $this->assertUrl(
                "tigger.com",
                array(false, false, false, "tigger.com", false, "/", "com", "", false));
        $this->assertUrl(
                "me.net/tigger",
                array(false, false, false, "me.net", false, "/tigger", "net", "", false));
    }
    
    public function testAsString()
    {
        $this->assertPreserved('https://www.here.com');
        $this->assertPreserved('http://me:secret@www.here.com');
        $this->assertPreserved('http://here/there');
        $this->assertPreserved('http://here/there?a=A&b=B');
        $this->assertPreserved('http://here/there?a=1&a=2');
        $this->assertPreserved('http://here/there?a=1&a=2?9,8');
        $this->assertPreserved('http://host?a=1&a=2');
        $this->assertPreserved('http://host#stuff');
        $this->assertPreserved('http://me:secret@www.here.com/a/b/c/here.html?a=A?7,6');
        $this->assertPreserved('http://www.here.com/?a=A__b=B');
        $this->assertPreserved('http://www.example.com:8080/');
    }
    
    public function testUrlWithTwoSlashesInPath()
    {
        $url = new SimpleUrl('/article/categoryedit/insert//');
        $this->assertEqual($url->getPath(), '/article/categoryedit/insert//');
    }
    
    public function testUrlWithRequestKeyEncoded()
    {
        $url = new SimpleUrl('/?foo%5B1%5D=bar');
        $this->assertEqual($url->getEncodedRequest(), '?foo%5B1%5D=bar');
        $url->addRequestParameter('a[1]', 'b[]');
        $this->assertEqual($url->getEncodedRequest(), '?foo%5B1%5D=bar&a%5B1%5D=b%5B%5D');

        $url = new SimpleUrl('/');
        $url->addRequestParameter('a[1]', 'b[]');
        $this->assertEqual($url->getEncodedRequest(), '?a%5B1%5D=b%5B%5D');
    }

    public function testUrlWithRequestKeyEncodedAndParamNamLookingLikePair()
    {
        $url = new SimpleUrl('/');
        $url->addRequestParameter('foo[]=bar', '');
        $this->assertEqual($url->getEncodedRequest(), '?foo%5B%5D%3Dbar=');
        $url = new SimpleUrl('/?foo%5B%5D%3Dbar=');
        $this->assertEqual($url->getEncodedRequest(), '?foo%5B%5D%3Dbar=');
    }

    public function assertUrl($raw, $parts, $params = false, $coords = false)
    {
        if (! is_array($params)) {
            $params = array();
        }
        $url = new SimpleUrl($raw);
        $this->assertIdentical($url->getScheme(), $parts[0], "[$raw] scheme -> %s");
        $this->assertIdentical($url->getUsername(), $parts[1], "[$raw] username -> %s");
        $this->assertIdentical($url->getPassword(), $parts[2], "[$raw] password -> %s");
        $this->assertIdentical($url->getHost(), $parts[3], "[$raw] host -> %s");
        $this->assertIdentical($url->getPort(), $parts[4], "[$raw] port -> %s");
        $this->assertIdentical($url->getPath(), $parts[5], "[$raw] path -> %s");
        $this->assertIdentical($url->getTld(), $parts[6], "[$raw] tld -> %s");
        $this->assertIdentical($url->getEncodedRequest(), $parts[7], "[$raw] encoded -> %s");
        $this->assertIdentical($url->getFragment(), $parts[8], "[$raw] fragment -> %s");
        if ($coords) {
            $this->assertIdentical($url->getX(), $coords[0], "[$raw] x -> %s");
            $this->assertIdentical($url->getY(), $coords[1], "[$raw] y -> %s");
        }
    }
    
    public function assertPreserved($string)
    {
        $url = new SimpleUrl($string);
        $this->assertEqual($url->asString(), $string);
    }
}

class TestOfAbsoluteUrls extends UnitTestCase
{
    public function testDirectoriesAfterFilename()
    {
        $string = '../../index.php/foo/bar';
        $url = new SimpleUrl($string);
        $this->assertEqual($url->asString(), $string);
        
        $absolute = $url->makeAbsolute('http://www.domain.com/some/path/');
        $this->assertEqual($absolute->asString(), 'http://www.domain.com/index.php/foo/bar');
    }

    public function testMakingAbsolute()
    {
        $url = new SimpleUrl('../there/somewhere.php');
        $this->assertEqual($url->getPath(), '../there/somewhere.php');
        $absolute = $url->makeAbsolute('https://host.com:1234/I/am/here/');
        $this->assertEqual($absolute->getScheme(), 'https');
        $this->assertEqual($absolute->getHost(), 'host.com');
        $this->assertEqual($absolute->getPort(), 1234);
        $this->assertEqual($absolute->getPath(), '/I/am/there/somewhere.php');
    }
    
    public function testMakingAnEmptyUrlAbsolute()
    {
        $url = new SimpleUrl('');
        $this->assertEqual($url->getPath(), '');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEqual($absolute->getScheme(), 'http');
        $this->assertEqual($absolute->getHost(), 'host.com');
        $this->assertEqual($absolute->getPath(), '/I/am/here/page.html');
    }
    
    public function testMakingAnEmptyUrlAbsoluteWithMissingPageName()
    {
        $url = new SimpleUrl('');
        $this->assertEqual($url->getPath(), '');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEqual($absolute->getScheme(), 'http');
        $this->assertEqual($absolute->getHost(), 'host.com');
        $this->assertEqual($absolute->getPath(), '/I/am/here/');
    }
    
    public function testMakingAShortQueryUrlAbsolute()
    {
        $url = new SimpleUrl('?a#b');
        $this->assertEqual($url->getPath(), '');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEqual($absolute->getScheme(), 'http');
        $this->assertEqual($absolute->getHost(), 'host.com');
        $this->assertEqual($absolute->getPath(), '/I/am/here/');
        $this->assertEqual($absolute->getEncodedRequest(), '?a');
        $this->assertEqual($absolute->getFragment(), 'b');
    }
    
    public function testMakingADirectoryUrlAbsolute()
    {
        $url = new SimpleUrl('hello/');
        $this->assertEqual($url->getPath(), 'hello/');
        $this->assertEqual($url->getBasePath(), 'hello/');
        $this->assertEqual($url->getPage(), '');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEqual($absolute->getPath(), '/I/am/here/hello/');
    }
    
    public function testMakingARootUrlAbsolute()
    {
        $url = new SimpleUrl('/');
        $this->assertEqual($url->getPath(), '/');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEqual($absolute->getPath(), '/');
    }
    
    public function testMakingARootPageUrlAbsolute()
    {
        $url = new SimpleUrl('/here.html');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/page.html');
        $this->assertEqual($absolute->getPath(), '/here.html');
    }
    
    public function testCarryAuthenticationFromRootPage()
    {
        $url = new SimpleUrl('here.html');
        $absolute = $url->makeAbsolute('http://test:secret@host.com/');
        $this->assertEqual($absolute->getPath(), '/here.html');
        $this->assertEqual($absolute->getUsername(), 'test');
        $this->assertEqual($absolute->getPassword(), 'secret');
    }
    
    public function testMakingCoordinateUrlAbsolute()
    {
        $url = new SimpleUrl('?1,2');
        $this->assertEqual($url->getPath(), '');
        $absolute = $url->makeAbsolute('http://host.com/I/am/here/');
        $this->assertEqual($absolute->getScheme(), 'http');
        $this->assertEqual($absolute->getHost(), 'host.com');
        $this->assertEqual($absolute->getPath(), '/I/am/here/');
        $this->assertEqual($absolute->getX(), 1);
        $this->assertEqual($absolute->getY(), 2);
    }
    
    public function testMakingAbsoluteAppendedPath()
    {
        $url = new SimpleUrl('./there/somewhere.php');
        $absolute = $url->makeAbsolute('https://host.com/here/');
        $this->assertEqual($absolute->getPath(), '/here/there/somewhere.php');
    }
    
    public function testMakingAbsoluteBadlyFormedAppendedPath()
    {
        $url = new SimpleUrl('there/somewhere.php');
        $absolute = $url->makeAbsolute('https://host.com/here/');
        $this->assertEqual($absolute->getPath(), '/here/there/somewhere.php');
    }
    
    public function testMakingAbsoluteHasNoEffectWhenAlreadyAbsolute()
    {
        $url = new SimpleUrl('https://test:secret@www.lastcraft.com:321/stuff/?a=1#f');
        $absolute = $url->makeAbsolute('http://host.com/here/');
        $this->assertEqual($absolute->getScheme(), 'https');
        $this->assertEqual($absolute->getUsername(), 'test');
        $this->assertEqual($absolute->getPassword(), 'secret');
        $this->assertEqual($absolute->getHost(), 'www.lastcraft.com');
        $this->assertEqual($absolute->getPort(), 321);
        $this->assertEqual($absolute->getPath(), '/stuff/');
        $this->assertEqual($absolute->getEncodedRequest(), '?a=1');
        $this->assertEqual($absolute->getFragment(), 'f');
    }
    
    public function testMakingAbsoluteCarriesAuthenticationWhenAlreadyAbsolute()
    {
        $url = new SimpleUrl('https://www.lastcraft.com');
        $absolute = $url->makeAbsolute('http://test:secret@host.com/here/');
        $this->assertEqual($absolute->getHost(), 'www.lastcraft.com');
        $this->assertEqual($absolute->getUsername(), 'test');
        $this->assertEqual($absolute->getPassword(), 'secret');
    }
    
    public function testMakingHostOnlyAbsoluteDoesNotCarryAnyOtherInformation()
    {
        $url = new SimpleUrl('http://www.lastcraft.com');
        $absolute = $url->makeAbsolute('https://host.com:81/here/');
        $this->assertEqual($absolute->getScheme(), 'http');
        $this->assertEqual($absolute->getHost(), 'www.lastcraft.com');
        $this->assertIdentical($absolute->getPort(), false);
        $this->assertEqual($absolute->getPath(), '/');
    }
}

class TestOfFrameUrl extends UnitTestCase
{
    public function testTargetAttachment()
    {
        $url = new SimpleUrl('http://www.site.com/home.html');
        $this->assertIdentical($url->getTarget(), false);
        $url->setTarget('A frame');
        $this->assertIdentical($url->getTarget(), 'A frame');
    }
}

/**
 * @note Based off of http://www.mozilla.org/quality/networking/testing/filetests.html
 */
class TestOfFileUrl extends UnitTestCase
{
    public function testMinimalUrl()
    {
        $url = new SimpleUrl('file:///');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/');
    }
    
    public function testUnixUrl()
    {
        $url = new SimpleUrl('file:///fileInRoot');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/fileInRoot');
    }
    
    public function testDOSVolumeUrl()
    {
        $url = new SimpleUrl('file:///C:/config.sys');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/C:/config.sys');
    }
    
    public function testDOSVolumePromotion()
    {
        $url = new SimpleUrl('file://C:/config.sys');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/C:/config.sys');
    }
    
    public function testDOSBackslashes()
    {
        $url = new SimpleUrl('file:///C:\config.sys');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/C:/config.sys');
    }
    
    public function testDOSDirnameAfterFile()
    {
        $url = new SimpleUrl('file://C:\config.sys');
        $this->assertEqual($url->getScheme(), 'file');
        $this->assertIdentical($url->getHost(), false);
        $this->assertEqual($url->getPath(), '/C:/config.sys');
    }
}

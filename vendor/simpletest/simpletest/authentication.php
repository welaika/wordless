<?php

require_once __DIR__ . '/http.php';

/**
 * Represents a single security realm's identity.
 */
class SimpleRealm
{
    private $type;
    private $root;
    private $username;
    private $password;

    /**
     * Starts with the initial entry directory.
     *
     * @param string $type      Authentication type for this realm. Only Basic
     *                          authentication is currently supported.
     * @param SimpleUrl $url    Somewhere in realm.
     */
    public function __construct($type, $url)
    {
        $this->type     = $type;
        $this->root     = $url->getBasePath();
        $this->username = false;
        $this->password = false;
    }

    /**
     * Adds another location to the realm.
     *
     * @param SimpleUrl $url    Somewhere in realm.
     */
    public function stretch($url)
    {
        $this->root = $this->getCommonPath($this->root, $url->getPath());
    }

    /**
     * Finds the common starting path.
     *
     * @param string $first        Path to compare.
     * @param string $second       Path to compare.
     *
     * @return string              Common directories.
     */
    protected function getCommonPath($first, $second)
    {
        $first  = explode('/', $first);
        $second = explode('/', $second);
        for ($i = 0; $i < min(count($first), count($second)); $i++) {
            if ($first[$i] != $second[$i]) {
                return implode('/', array_slice($first, 0, $i)) . '/';
            }
        }

        return implode('/', $first) . '/';
    }

    /**
     * Sets the identity to try within this realm.
     *
     * @param string $username    Username in authentication dialog.
     * @param string $username    Password in authentication dialog.
     */
    public function setIdentity($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Accessor for current identity.
     *
     * @return string        Last succesful username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Accessor for current identity.
     *
     * @return string        Last succesful password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Test to see if the URL is within the directory tree of the realm.
     *
     * @param SimpleUrl $url    URL to test.
     *
     * @return bool          True if subpath.
     */
    public function isWithin($url)
    {
        if ($this->isIn($this->root, $url->getBasePath())) {
            return true;
        }
        if ($this->isIn($this->root, $url->getBasePath() . $url->getPage() . '/')) {
            return true;
        }

        return false;
    }

    /**
     * Tests to see if one string is a substring of another.
     *
     * @param string $part        Small bit.
     * @param string $whole       Big bit.
     *
     * @return bool            True if the small bit is in the big bit.
     */
    protected function isIn($part, $whole)
    {
        return strpos($whole, $part) === 0;
    }
}

/**
 * Manages security realms.
 */
class SimpleAuthenticator
{
    private $realms;

    /**
     * Clears the realms.
     */
    public function __construct()
    {
        $this->restartSession();
    }

    /**
     * Starts with no realms set up.
     */
    public function restartSession()
    {
        $this->realms = array();
    }

    /**
     * Adds a new realm centered the current URL. Browsers privatey wildly on
     * their behaviour in this regard. Mozilla ignores the realm and presents
     * only when challenged, wasting bandwidth. IE just carries on presenting
     * until a new challenge occours. SimpleTest tries to follow the spirit of
     * the original standards committee and treats the base URL as the root of a
     * file tree shaped realm.
     *
     * @param SimpleUrl $url    Base of realm.
     * @param string $type      Authentication type for this realm. Only
     * Basicauthentication is currently supported.
     * @param string $realm     Name of realm.
     */
    public function addRealm($url, $type, $realm)
    {
        $this->realms[$url->getHost()][$realm] = new SimpleRealm($type, $url);
    }

    /**
     * Sets the current identity to be presented against that realm.
     *
     * @param string $host        Server hosting realm.
     * @param string $realm       Name of realm.
     * @param string $username    Username for realm.
     * @param string $password    Password for realm.
     */
    public function setIdentityForRealm($host, $realm, $username, $password)
    {
        if (isset($this->realms[$host][$realm])) {
            $this->realms[$host][$realm]->setIdentity($username, $password);
        }
    }

    /**
     * Finds the name of the realm by comparing URLs.
     *
     * @param SimpleUrl $url        URL to test.
     *
     * @return SimpleRealm          Name of realm.
     */
    protected function findRealmFromUrl($url)
    {
        if (! isset($this->realms[$url->getHost()])) {
            return false;
        }
        foreach ($this->realms[$url->getHost()] as $name => $realm) {
            if ($realm->isWithin($url)) {
                return $realm;
            }
        }

        return false;
    }

    /**
     * Presents the appropriate headers for this location.
     *
     * @param SimpleHttpRequest $request  Request to modify.
     * @param SimpleUrl $url              Base of realm.
     */
    public function addHeaders($request, $url)
    {
        if ($url->getUsername() && $url->getPassword()) {
            $username = $url->getUsername();
            $password = $url->getPassword();
        } elseif ($realm = $this->findRealmFromUrl($url)) {
            $username = $realm->getUsername();
            $password = $realm->getPassword();
        } else {
            return;
        }
        $this->addBasicHeaders($request, $username, $password);
    }

    /**
     * Presents the appropriate headers for this location for basic authentication.
     *
     * @param SimpleHttpRequest $request  Request to modify.
     * @param string $username            Username for realm.
     * @param string $password            Password for realm.
     */
    public static function addBasicHeaders(&$request, $username, $password)
    {
        if ($username && $password) {
            $request->addHeaderLine(
                'Authorization: Basic ' . base64_encode("$username:$password"));
        }
    }
}

SimpleTest [![Build Status](https://travis-ci.org/simpletest/simpletest.svg)](https://travis-ci.org/simpletest/simpletest) [![Latest Stable Version](https://img.shields.io/packagist/v/simpletest/simpletest.svg?style=flat-square)](https://packagist.org/packages/simpletest/simpletest) [![Total Downloads](https://img.shields.io/packagist/dt/simpletest/simpletest.svg?style=flat-square)](https://packagist.org/packages/simpletest/simpletest) [![Latest Unstable Version](https://poser.pugx.org/simpletest/simpletest/v/unstable)](https://packagist.org/packages/simpletest/simpletest)
==========

SimpleTest is a framework for unit testing, web site testing and mock objects for PHP.

### Installation

#### Downloads

All downloads are stored on Github Releases.

You may find the zip of the "latest released/tagged version" here:

https://github.com/simpletest/simpletest/releases/latest

You may find the zip archive of the "dev-master" branch here:

https://github.com/simpletest/simpletest/archive/master.zip

#### Composer

You may also install the extension through Composer into the `/vendor` folder of your project.

Either run

    php composer.phar require --prefer-dist simpletest/simpletest "^1.1"

or add the package `simpletest/simpletest` to the require-dev section of your `composer.json` file:

    {
        "require-dev": {
            "simpletest/simpletest": "^1.1"
        }
    }

followed by running `composer install`.

### Issues

Please report all issues you encounter at [Github Issues](https://github.com/simpletest/simpletest/issues).

### Community

Feel free to [ask a new question on Stack Overflow](https://stackoverflow.com/questions/ask?tags=simpletest+php) or at [Github Issues](https://github.com/simpletest/simpletest/issues).

StackOverflow offers also a good collection of [SimpleTest related questions](https://stackoverflow.com/questions/tagged/simpletest).

### Requirements

PHP 7.1+

### Authors

- Marcus Baker
- Jason Sweat
- Travis Swicegood
- Perrick Penet
- Edward Z. Yang
- Jens A. Koch
- [Contributors on Github](https://github.com/simpletest/simpletest/graphs/contributors)

### License

GNU LGPL v2.1

### Tests

The unit tests for SimpleTest itself can be run here:

    test/all_tests.php

The acceptance tests require a running server:

    - php -S localhost:8080 -t test/site
    - test/acceptance_test.php

# Docs

    http://simpletest.org/en/first_test_tutorial.html

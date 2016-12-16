# JsPhpize
[![Latest Stable Version](https://poser.pugx.org/js-phpize/js-phpize/v/stable.png)](https://packagist.org/packages/js-phpize/js-phpize)
[![Build Status](https://travis-ci.org/pug-php/js-phpize.svg?branch=master)](https://travis-ci.org/pug-php/js-phpize)
[![StyleCI](https://styleci.io/repos/65670436/shield?style=flat)](https://styleci.io/repos/65670436)
[![Test Coverage](https://codeclimate.com/github/pug-php/js-phpize/badges/coverage.svg)](https://codecov.io/github/pug-php/js-phpize?branch=master)
[![Code Climate](https://codeclimate.com/github/pug-php/js-phpize/badges/gpa.svg)](https://codeclimate.com/github/pug-php/js-phpize)

Convert js-like syntax to standalone PHP code.

## Install
In the root directory of your project, open a terminal and enter:
```shell
composer require js-phpize/js-phpize
```

Use compile to get PHP equivalent code to JavaScript input:
```php
use JsPhpize\JsPhpize;

$jsPhpize = new JsPhpize();

echo $jsPhpize->compile('foo = { bar: { "baz": "hello" } }');
```

This code will output the following PHP code:
```php
$foo = array( "bar" => array( "baz" => "hello" ) );
```

Or use render to execute it directly:
```php
use JsPhpize\JsPhpize;

$jsPhpize = new JsPhpize();

$code = '
    // Create an object
    foo = { bar: { "baz": "hello" } };
    key = 'bar'; // instanciate a string

    return foo[key].baz;
';

$value = $jsPhpize->render($code);

echo $value;
```

This will display ```hello```.

This library intend to is intended to allow js-like in PHP contexts (such as in template engines).

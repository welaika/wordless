# Pug.php
[![Latest Stable Version](https://poser.pugx.org/pug-php/pug/v/stable.png)](https://packagist.org/packages/pug-php/pug)
[![Monthly Downloads](https://poser.pugx.org/pug-php/pug/d/monthly)](https://packagist.org/packages/pug-php/pug)
[![Reference Status](https://www.versioneye.com/php/kylekatarnls:jade-php/reference_badge.svg?style=flat)](https://www.versioneye.com/php/kylekatarnls:jade-php/references)
[![License](https://poser.pugx.org/pug-php/pug/license)](https://packagist.org/packages/pug-php/pug)

[![Build Status](https://travis-ci.org/pug-php/pug.svg?branch=master)](https://travis-ci.org/pug-php/pug)
[![StyleCI](https://styleci.io/repos/59010999/shield?style=flat)](https://styleci.io/repos/59010999)
[![Test Coverage](https://codeclimate.com/github/pug-php/pug/badges/coverage.svg)](https://codecov.io/github/pug-php/pug?branch=master)
[![Code Climate](https://codeclimate.com/github/pug-php/pug/badges/gpa.svg)](https://codeclimate.com/github/pug-php/pug)


Pug.php adds inline PHP scripting support to the [Pug](http://jade-lang.com) template compiler.

> Pug has been recently re-named from Jade. If you're new to Pug, you should install the pug-php/pug package on composer.

##### [The Pug Syntax Reference](https://github.com/pugjs/pug#readme)
##### [See Pug.php demo](https://pug-demo.herokuapp.com/)

## Implementation details

The fork is a complete rewrite, all the code is ported from the original jade project.

All the features from the original are supported but undertested, including inheritance
and mixins.

### Install using composer
```sh
composer require pug-php/pug
composer install
```
[pug-php/pug on packagist.org](https://packagist.org/packages/pug-php/pug)

### Pug in your favorite framework

Phalcon: https://github.com/pug-php/pug-phalcon

Symfony: https://github.com/pug-php/pug-symfony

Laravel: https://github.com/BKWLD/laravel-pug

CodeIgniter: https://github.com/pug-php/ci-pug

### Pug options

Pug options should be passed to the Jade construction

```php
$pug = new Pug(array(
    'prettyprint' => true,
    'extension' => '.pug',
    'cache' => 'pathto/writable/cachefolder/'
);
```

### Supports for local variables

```php
$pug = new Pug();
$output = $pug->render('file', array(
    'title' => 'Hello World'
));
```

### Supports for custom filters

Filters must be callable: It can be a class that implements the *__invoke()* method, or an anonymous function.

```php
$pug->filter('escaped', 'My\Callable\Class');

// or

$pug->filter('escaped', function($node, $compiler){
    foreach ($node->block->nodes as $line) {
        $output[] = $compiler->interpolate($line->value);
    }
    return htmlentities(implode("\n", $output));
});
```

#### Built-in filters

* :css
* :php
* :javascript
* :escaped
* :cdata

#### Install other filters with composer

http://pug-filters.selfbuild.fr/

#### Publish your own filter

https://github.com/kylekatarnls/jade-filter-base#readme

### Supports for custom keywords

You can add custom keywords, here are some examples:

**Anonymous function**:
```php
$pug->addKeyword('for', function ($args) {
    return array(
        'beginPhp' => 'for (' . $args . ') {',
        'endPhp' => '}',
    );
});

$pug->render('
for $i = 1; $i <= 3; $i++
    p= i
');
```

This will render:
```html
<p>1</p>
<p>2</p>
<p>3</p>
```

Note that the existing ```for..in``` operator will have the precedance on this custom ```for``` keyword.

**Invokable class**:
```php
class UserKeyword
{
    public function __invoke($arguments, $block, $keyWord)
    {
        $badges = array();
        foreach ($block->nodes as $index => $tag) {
            if ($tag->name === 'badge') {
                $href = $tag->getAttribute('color');
                $badges[] = $href['value'];
                unset($block->nodes[$index]);
            }
        }

        return array(
            'begin' => '<div class="' . $keyWord . '" data-name="' . $arguments . '" data-badges="[' . implode(',', $badges) . ']">',
            'end' => '</div>',
        );
    }
}

$pug->addKeyword('user', new UserKeyword());

$pug->render('
user Bob
    badge(color="blue")
    badge(color="red")
    em Registered yesterday
');
```

This will render:
```html
<div class="user" data-name="Bob" data-badges="['blue', 'red']">
    <em>Registered yesterday</em>
</div>
```

A keyword must return an array (containing **begin** and/or **end** entires) or a string (used as a **begin** entry).

The **begin** and **end** are rendered as raw HTML, but you can also use **beginPhp** and **endPhp** as in the first example to render PHP codes that will wrap the rendered block if there is one.

### Cache

**Important**: to improve performance in production, enable the Pug cache by setting the **cache** option to a writable directory, you can first cache all your template at once (during deployment):
```php
$pug = new Pug(array(
    'cache' => 'var/cache/pug',
);
list($success, $errors) = $pug->cacheDirectory('path/to/pug/templates');
echo "$success files have been cached\n";
echo "$errors errors occurred\n";
```
Be sure any unexpected error occurred and that all your templates in your template directory have been cached.

Then use the same cache directory and template directory in production with the option upToDateCheck to ```false```to bypass the cache check and automatically use the cache version:
```php
$pug = new Pug(array(
    'cache' => 'var/cache/pug',
    'basedir' => 'path/to/pug/templates',
    'upToDateCheck' => false,
);
$pug->render('path/to/pug/templates/my-page.pug');
```

### Check requirements

To check if all requirements are ready to use Pug, use the requirements method:
```php
$pug = new Pug(array(
    'cache' => 'pathto/writable/cachefolder/'
);
$missingRequirements = array_keys(array_filter($pug->requirements(), function ($valid) {
    return $valid === false;
}));
$missings = count($missingRequirements);
if ($missings) {
    echo $missings . ' requirements are missing.<br />';
    foreach ($missingRequirements as $requirement) {
        switch($requirement) {
            case 'streamWhiteListed':
                echo 'Suhosin is enabled and ' . $pug->getOption('stream') . ' is not in suhosin.executor.include.whitelist, please add it to your php.ini file.<br />';
                break;
            case 'cacheFolderExists':
                echo 'The cache folder does not exists, please enter in a command line : <code>mkdir -p ' . $pug->getOption('cache') . '</code>.<br />';
                break;
            case 'cacheFolderIsWritable':
                echo 'The cache folder is not writable, please enter in a command line : <code>chmod -R +w ' . $pug->getOption('cache') . '</code>.<br />';
                break;
            default:
                echo $requirement . ' is false.<br />';
        }
    }
    exit();
}
```

## Contributing

All contributions are welcome, for any bug, issue or merge request (except for secutiry issues) please [refer to CONTRIBUTING.md](CONTRIBUTING.md)

## Security

Please report any security issue or risk by emailing pug@selfbuild.fr. Please don't disclose security bugs publicly until they have been handled by us.

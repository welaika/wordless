![Wordless logo](http://welaika.github.com/wordless/assets/images/wordless_new.png)

Wordless is a junction between a WordPress plugin and a theme boilerplate that dramatically speeds up and enhances your custom theme creation. Some of its features are:

* A structured, organized and clean [theme organization](https://wordless.readthedocs.io/en/latest/_pages/usage/anatomy.html)
* Bootstrap a new theme directly within wp-cli
* Write PHP templates with [Pug templating system](https://github.com/pug-php/pug)
* Write CSS stylesheets using the awesome [SCSS syntax](http://sass-lang.com)
* Out-of-the-box support to [Stylelint](https://stylelint.io/) configured for SCSS syntax.
* Write Javascript logic using modern syntax thanks to [Babel](https://babeljs.io/)
* Automatically polyfill (with [core-js](https://github.com/zloirock/core-js)) and transpile Javascript based on your support inside [`.browserslistrc`](https://github.com/browserslist/browserslist)
* A growing set of handy and documented PHP helper functions ready to be used within your views
* Preconfigured support to [MailHog](https://github.com/mailhog/MailHog) mail-catcher in development.
* Development workflow backed by [WebPack](https://github.com/webpack/webpack), [BrowserSync](https://www.browsersync.io/) (with live reload), [WP-CLI](http://wp-cli.org/), [Yarn](https://yarnpkg.com/en/). All the standards you already know, all the customizations you may need.

![Helpers tests](https://github.com/welaika/wordless/workflows/Test/badge.svg?branch=master)
[![Documentation Status](https://readthedocs.org/projects/wordless/badge/?version=latest)](https://wordless.readthedocs.io/en/latest/?badge=latest)

## Documentation

### Complete documentation

Read the complete Wordless documentation at [wordless.readthedocs.io](https://wordless.readthedocs.io/en/latest/?badge=latest) where you'll find - hopefully - all the informations about installation, usage guide, in depth explanation about the stack.

If you want to contribute to the documentation.

- Have docker started
- `docker build -t wordless_docs rtfm`
- `make documentation`
- `open rtfm/build/html/index.html`
- update the doc; documentation about the RST syntax can be found at https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html
- `commit` and `push`; the `rtfm/` folder on master will be auto-deployed on https://wordless.readthedocs.io/

Every subsequent `make documentation` will refresh your HTML.

### Built-in helpers documentation

You can find it at http://welaika.github.io/wordless/docs/latest/html/index.html.

If you are interested in contributing to the documentation:

- we are documenting php files in

```
 wordless/helpers/*
```

- here is a [list](http://welaika.github.io/wordless/docs/latest/html/dd/da0/todo.html) of documentation gaps :9
- `brew install doxygen`
- go and add doc following the [doxygen](http://www.stack.nl/~dimitri/doxygen/) conventions
- compile the new doc with `doxygen docs/Doxyfile` (from project's root)
- you'll have an untracked folder `docs/build` in GiT. Leave it alone and `git checkout gh-pages` instead.
- `mv docs/build docs/latest` overwriting the old one
- `commit` and `push` the branch

## Development

### Deploy

* Merge your feature branch - with passing tests - in `master` with
  `git checkout master && git merge --no-ff feature` or by pull request
* On `master` update the plugin version (SEMVER) in `./wordless.php` ("Version")
  and `readme.txt` ("Stable tag") files and commit the updated files.
* do `git tag x.y.x` where *x.y.z* equals to the previously written version.
* `git push origin master --tags` to push both commits and tags
* update the changelog for the new release at https://github.com/welaika/wordless/releases

Automations will do the leftovers, including to publish updated documentation on ReadTheDocs and
the plugin on https://wordpress.org/plugins/wordless/

## Changelog

A changelog for each tag/relase is mandatory to be compiled at
https://github.com/welaika/wordless/releases.

## Additional recommended plugins and tools

![](https://raw.githubusercontent.com/welaika/wordmove/master/assets/images/wordmove.png)

[Wordmove](https://github.com/welaika/wordmove): a great gem (from yours truly) to automatically mirror local WordPress installations and DB data back and forth from your local development machine to the remote staging server;

## Known limitations

* Wordless has not been tested on Windows

## Deprecations

### 4.0

Default configuration has dropped support for CoffeeScritp and for SASS indented syntax. Obviously you are free to change Webpack's config once you've created the theme, but now we officially support ES2015 and SCSS instead.

### 3.0

Ruby-based preprocessors and the `WORDLESS_LEGACY` configuration are definitely dropped.
Theme's folder structure changed.

### 2.5

Wordless 2.5 deprecates the old ruby preprocessor support. It is disabled by default. If you need to develop an old theme you need to explicitely activate them by setting the following in your `wp-config.php`:

```php
define('WORDLESS_LEGACY', true);
```

We plan to completely remove this support in Wordless 3.

## Localization

Wordless is available in English, German, Greek, Italian and Spanish, at the moment.

The user interface was translated by Wasilis Mandratzis-Walz (German and Greek), David Mejorado (Spanish).

Your help is welcome! Add your own language using [Transifex](https://www.transifex.com/projects/p/wordless/).

## Need more tools?
Visit [WordPress Tools](https://www.wptools.it).

## Third Part Libraries

* [Mobile Detect](http://mobiledetect.net)

## Author

made with ❤️ and ☕️ by [weLaika](https://dev.welaika.com)

## License

(The MIT License)

Copyright © 2011-2019 [weLaika](https://dev.welaika.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the ‘Software’), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED ‘AS IS’, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

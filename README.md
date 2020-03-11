![Wordless logo](http://welaika.github.com/wordless/assets/images/wordless_new.png)

Wordless is an opinionated WordPress plugin that dramatically speeds up and enhances your custom theme creation. Some of its features are:

* A structured, organized and clean [theme organization](https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme)
* Bootstrap a new theme directly within wp-cli
* Write PHP templates with [Pug templating system](https://github.com/pug-php/pug)
* Write CSS stylesheets using the awesome [SCSS syntax](http://sass-lang.com)
* Out-of-the-box support to [Stylelint](https://stylelint.io/) configured for SCSS syntax.
* Write Javascript logic in ES2015 thanks to [Babel](https://babeljs.io/)
* Automatically polyfill (with [core-js](https://github.com/zloirock/core-js)) and transpile Javascript based on your support inside [`.browserslistrc`](https://github.com/browserslist/browserslist)
* A growing set of handy and documented PHP helper functions ready to be used within your views
* Development workflow backed by [WebPack](https://github.com/webpack/webpack), [BrowserSync](https://www.browsersync.io/) (with live reload), [WP-CLI](http://wp-cli.org/), [Yarn](https://yarnpkg.com/en/). All the standards you already know, all the customizations you may need.

[![Build Status](https://travis-ci.org/welaika/wordless.svg?branch=master)](http://travis-ci.org/welaika/wordless)
[![Documentation Status](https://readthedocs.org/projects/wordless/badge/?version=latest)](https://wordless.readthedocs.io/en/latest/?badge=latest)

## Getting started

### Wordless GEM

The quickest CLI tool to setup a new WordPress locally. Wordless ready.
Navigate to https://github.com/welaika/wordless_gem to discover the tool and set up all you need for local development. In less than 2 minutes ;)

### (Not so) Manual

**Prerequisites**

1. Install WP-CLI http://wp-cli.org/#installing
2. Install global packages from NPM: `npm install -g foreman yarn`

Once done, assuming you have a standard WordPress installation already up and running and you are in its root directory:

1. `wp plugin install wordless`
2. `wp plugin activate wordless`
3. `wp wordless theme create mybrandnewtheme`
4. `cd wp-content/themes/mybrandnewtheme`
5. `yarn install`

Now you have all you need to start developing 💻; just be sure you are in your theme directory and run

`yarn server`

webpack, php server and your browser will automatically come up and serve your needs :)

## GiT and Deployment

Wordless >=2 is backed by *WebPack* for all build tasks (it's up to you to eventually extend it to other behaviours).

Those compiled assets **must** obviously **be deployed** on the remote server.

PUG templates are compiled by the WP plugin at runtime and cached in the theme's `tmp` folder. So just `gitignore tmp/*` and no other worries.

We provide a minimal `.gitignore` inside the theme's folder:

```git
tmp/*
!tmp/.gitkeep
assets/*/*
!assets/*/.gitkeep
!assets/fonts
.DS_Store
node_modules

```

## Insights

[Wordless 2. Re-embracing a modern flow.](https://dev.welaika.com/blog/2017/12/17/wordless-2-0.html) - An historical overview about the history of Wordless and why it moved from Ruby to Node.

[How does it work](#) - Basic concepts (WIP)

[Wordless theme anatomy](https://wordless.readthedocs.io/en/latest/_pages/usage/anatomy.html) - Structure and conventions

## Additional recommended plugins and tools

Wordless is not meant to be a bloated, all-inclusive tool. This is why we recommend adding some other plugins to get the most out of your beautiful WP developer life.

<img src="http://welaika.github.com/wordless-extender/assets/images/wordless-extender.png" align="right" style="max-width: 50%" />

* We are developing [Wordless-extender](https://github.com/welaika/wordless-extender). A little plugin, that brings our collection of favorite plugins and let you set up some constants in _wp-config.php_, useful for hardening the WP installation. At the moment it is not yet well documented and it's not in the wordpress.org repository, but we are moving fast, so keep following!

_______________

* [Wordmove](https://github.com/welaika/wordmove): a great gem (from yours truly) to automatically mirror local WordPress installations and DB data back and forth from your local development machine to the remote staging server;

## Known problems and limitations

* Wordless has not been tested on Windows machines
* The routing part can be dramatically improved to make it more readable and DRY

## Deprecations

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

## Documentation

### Complete documentation

Wordless full documentation - [wordless.readthedocs.io](https://wordless.readthedocs.io/en/latest/?badge=latest)

If you want to contribute to the documentation.

* `brew install sphinx-doc`
* `cd rtfm/`
* `sphinx-autobuild source/ build/html` and open http://127.0.0.1:8000 to watch the preview
  * the build server will autoreload your page on modifications
* update the doc; documentation about the RST syntax can be found at
  https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html
* `commit` and `push`; the `rtfm/` folder on master will be auto-deployed on https://wordless.readthedocs.io/

As a side note: [reStructuredText](https://marketplace.visualstudio.com/items?itemName=lextudio.restructuredtext)
plugin in VSCode could be of great help linting, highlighting and previewing your texts.

### Helpers documentation

You can find it at http://welaika.github.io/wordless/docs/latest/html/index.html.

If you are interested in contributing to the documentation:

* we are documenting the following at the moment (path recursive)

```
wordless/helpers/*
```

* here is a [list](http://welaika.github.io/wordless/docs/latest/html/dd/da0/todo.html) of documentation gaps :9
* `brew install doxygen`
* go and add doc following the [doxygen](http://www.stack.nl/~dimitri/doxygen/) conventions
* compile the new doc with `doxygen docs/Doxyfile` (from project's root)
* you'll have an untracked folder `docs/build` in GiT. Leave it alone and `git checkout gh-pages`
  instead.
* `mv docs/build docs/latest` overwriting the old one
* `commit` and `push` the branch

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

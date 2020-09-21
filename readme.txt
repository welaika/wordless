=== Plugin Name ===

Contributors: welaika, stefano.verna
Donate link: https://github.com/welaika/wordless
Tags: sass, pug, jade, webpack, scss, npm, yarn, babel, es6
Requires at least: 3.0
Tested up to: 5.3.0
Stable tag: 4.1.0
License: The MIT License
License URI: http://www.opensource.org/licenses/MIT

Wordless is an opinionated WordPress plugin that dramatically speeds up and enhances your custom theme creation. Some of its features are:

* A structured, organized and clean [theme organization](https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme)
* Bootstrap a new theme directly within wp-cli
* Write PHP templates with [Pug templating system](https://github.com/pug-php/pug)
* Write CSS stylesheets using the awesome [SCSS syntax](http://sass-lang.com)
* Out-of-the-box support to [Stylelint](https://stylelint.io/) configured for SCSS syntax.
* Write Javascript logic in ES2015 thanks to [Babel](https://babeljs.io/)
* Automatically polyfill (with [core-js](https://github.com/zloirock/core-js)) and transpile Javascript based on your support inside [`.browserslistrc`](https://github.com/browserslist/browserslist)
* A growing set of handy and documented PHP helper functions ready to be used within your views
* Preconfigured support to [MailHog](https://github.com/mailhog/MailHog) mail-catcher.
* Development workflow backed by [WebPack](https://github.com/webpack/webpack), [BrowserSync](https://www.browsersync.io/) (with live reload), [WP-CLI](http://wp-cli.org/), [Yarn](https://yarnpkg.com/en/). All the standards you already know, all the customizations you may need.

[![Build Status](https://travis-ci.org/welaika/wordless.svg?branch=master)](http://travis-ci.org/welaika/wordless)
[![Documentation Status](https://readthedocs.org/projects/wordless/badge/?version=latest)](https://wordless.readthedocs.io/en/latest/?badge=latest)

## Documentation

### Complete documentation

Read the complete Wordless documentation at [wordless.readthedocs.io](https://wordless.readthedocs.io/en/latest/?badge=latest) where you'll find - hopefully - all the informations about installation, usage guide, in depth explanation about the stack.

### Buil-in helpers documentation

You can find it at http://welaika.github.io/wordless/docs/latest/html/index.html.


== Changelog ==

You can find the changelog @ https://github.com/welaika/wordless/releases

== Upgrade Notice ==

= 3.0.0 =

* Haml is no longer supported
* This version of wordless is not compatible with theme structure from <3.0.0.
  It's up to you to adapt your folders accordingly if you'd like to update the plugin.

= 2.6.1 =

Added helper for Gutenberg/ACF blocks.

= 2.5 =

* Old ruby preprocessors are no more activated by default. Please refer to
the changelog for more info.

= 0.5 =

* WARNING! Version 0.5 is not backward compatible! Wordless had always used `yield()` function in its template; starting from some point yield() become a reseved PHP function, so we had to rename it in Wordless code. If you get errors search and replace `yield()` inside the pugin directory with `wl_yield()`. Sorry for the inconvenient.

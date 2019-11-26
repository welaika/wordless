=== Plugin Name ===

Contributors: welaika, stefano.verna
Donate link: https://github.com/welaika/wordless
Tags: sass, pug, jade, webpack, scss, npm, yarn
Requires at least: 3.0
Tested up to: 5.3.0
Stable tag: 3.0.0
License: The MIT License
License URI: http://www.opensource.org/licenses/MIT

Wordless is an opinionated WordPress plugin that dramatically speeds up and enhances your custom theme creation. Some of its features are:

* A structured, organized and clean [theme organization](https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme)
* Bootstrap a new theme directly within wp-cli
* Write PHP templates with [Pug templating system](https://github.com/pug-php/pug)
* Write CSS stylesheets using the awesome [Sass syntax](http://sass-lang.com)
* Out-of-the-box support to [Stylelint](https://stylelint.io/) configured for indented SASS syntax.
* Write Javascript logic in [Coffeescript](http://jashkenas.github.com/coffee-script/)
* Automatically polyfill and transpile your JS setting your support inside [`.browserslistrc`](https://github.com/browserslist/browserslist)
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

Read more on [GitHub](https://github.com/welaika/wordless) and on
[Read the Docs](https://wordless.readthedocs.io/en/latest/)


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

Wordless's documentation
========================

.. image:: _static/images/icon-256x256.png

Introduction
############

Wordless is an opinionated WordPress plugin + starter theme that dramatically
speeds up and enhances your custom theme creation. Some of its features are:

* A structured, organized and clean theme organization
* **Scaffold** a new theme directly within wp-cli
* Write PHP templates with **PUG** templating language
* Write CSS stylesheets using the awesome **SCSS** syntax
* Write Javascript logic in **ES2015**
* A growing set of handy and documented PHP **helper functions** ready to be used
  within your views
* Preconfigured support to **MailHog** mail-catcher.
* Development workflow backed by **WebPack**, BrowserSync (with live reload),
  WP-CLI, Yarn. All the standards you already know, all the customizations you
  may need.

Wordless is a micro-framework for custom themes development. Thus is a product intended
for developers.

A compiled Wordless theme will run on any standard Wordpress installation.

Wordless does not alter any core functionality, thus it is compatible with reasonably
any generic plugin.

Table of Contents
#################

.. toctree::
    :caption: Installation

    _pages/install/prerequisites
    _pages/install/gem
    _pages/install/manual

.. toctree::
    :caption: Usage and structure

    _pages/usage/anatomy
    _pages/usage/routing
    _pages/usage/rendering_pug
    _pages/usage/css_js
    _pages/usage/helpers
    _pages/usage/initializers
    _pages/usage/locales
    _pages/usage/filters
    _pages/usage/cli
    _pages/usage/test_suite

.. toctree::
    :caption: Build and deployment

    _pages/build/intro
    _pages/build/dev
    _pages/build/prod
    _pages/build/deploy

.. toctree::
    :caption: Development stack

    _pages/stack/intro
    _pages/stack/node
    _pages/stack/server
    _pages/stack/compile

.. toctree::
    :caption: Appendix

    _pages/appendix/render_static
    _pages/appendix/acf_gutenberg_blocks
    _pages/appendix/plainphp

TODOs
#####

A list of known bugs, wip and improvements this documentation needs, hoping it will be kept empty ;)

.. todolist::

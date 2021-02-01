.. _Anatomy:

Theme anatomy
=============

This is a typical `Wordless theme directory structure`_ (as per latest release):
::

  vanilla_theme/
  ├── .vscode
  │   ├── launch.json
  │   └── settings.json
  ├── config
  │   ├── initializers
  │   └── locales
  ├── dist
  │   ├── fonts
  │   ├── images
  │   ├── javascripts
  │   ├── stylesheets
  │   └── README.md
  ├── helpers
  │   ├── ComponentPost.php
  │   └── README.mdown
  ├── src
  │   ├── images
  │   ├── javascripts
  │   ├── stylesheets
  │   └── main.js
  ├── tests
  │   ├── _data
  │   ├── _output
  │   ├── _support
  │   ├── acceptance
  │   ├── functional
  │   ├── unit
  │   ├── wpunit
  │   ├── acceptance.suite.yml
  │   ├── functional.suite.yml
  │   ├── unit.suite.yml
  │   └── wpunit.suite.yml
  ├── tmp
  │   └── .gitkeep
  ├── views
  │   ├── components
  │   ├── layouts
  │   ├── partials
  │   └── posts
  ├── .browserslistrc
  ├── .env
  ├── .env.ci
  ├── .env.testing
  ├── .eslintrc.json
  ├── .gitignore
  ├── .nvmrc
  ├── .stylelintignore
  ├── .stylelintrc.json
  ├── Procfile
  ├── Procfile.testing
  ├── codeception.ci.yml
  ├── codeception.dist.yml
  ├── composer.json
  ├── index.php
  ├── package.json
  ├── release.txt
  ├── screenshot.png
  ├── style.css
  ├── webpack.config.js
  ├── webpack.env.js
  └── yarn.lock

.. _Wordless theme directory structure : https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme

Next chapters will deepen into each part of the structure, in reasoned order.

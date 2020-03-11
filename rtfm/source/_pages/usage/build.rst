.. _Build:

Build and distribution
======================

Since Wordless uses Webpack, we have to manage build and distribution
strategies for dev and staging/production.

The source asset code is placed in
``src/{javascripts|stylesheets|images}``, while built/optimized code
is placed - automatically by Webpack - in
``dist/{javascripts|stylesheets|images}``

.. seealso::

    :ref:`JS and SCSS`

We offer standard approaches for both environments. They are handled -
as expected - through ``package.json`` 's scripts [#f1]_:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :lines: 13-24
    :language: javascript
    :caption: package.json

It is expected - but it's still up to you - that before every build you will
clean the compiled files. ``yarn clean:dist`` will do the cleanup.

Build for development
#####################

.. code-block:: bash

    yarn clean:dist && yarn build:dev

.. note::

    Most of the time you'll be working using the built-in development server
    through ``yarn server``, but invoking a build arbitrarily is often useful.

Build for production
####################

.. code-block:: bash

    yarn clean:dist && yarn build:prod

Production build will essentially:

* enable Webpack's `production mode`_
* do not produce source maps for CSS
* do minimize assets

.. note::
    By default the production build **will** produce source-maps for JS; this is done to
    lower the debugging effort, to respect the readability of the source code in users'
    browser and to simplify the shipping of source-maps to error monitoring softwares such
    as Sentry.

    You can easily disable this behaviour setting ``devtool: false`` in ``webpack.env.js``
    inside the ``prodOptions`` object.

Release signature
^^^^^^^^^^^^^^^^^

You notice that ``build:prod`` script will invoke ``sign-release`` too.
The latter will write the SHA of the current GiT commit into the
``release.txt`` file in the root of the theme.

You can easily disable this behaviour if you'd like to.

``release.txt`` is implemented to have a reference of the code version deployed
in production and to integrate external services that should requires release
versioning (for us in Sentry).

Code linting
############

Wordless ships with preconfigured linting of SCSS
using `Stylelint`_.

It is configured in ``.stylelintrc.json``, you can add exclusion in
``.stylelintignore``; all is really standard.

The script ``yarn lint`` is preconfigured to run the the lint tasks.

.. tip::

    Code linting could be chained in a build script, e.g.:

    .. code-block::

        "build:prod": "yarn lint && webpack -p --bail --env.NODE_ENV=production"

.. tip::

    Code linting could be integrated inside a `Wordmove hook`_

.. tip::

    You can force linting on a pre-commit basis integrating Husky_
    in your workflow.


.. _Stylelint: https://stylelint.io/
.. _Wordmove hook: https://github.com/welaika/wordmove/wiki/Hooks
.. _Husky: https://github.com/typicode/husky

.. _PHUG optimizer:

PHUG optimizer
##############

When performance is a must, PHUG ships a built-in `Optimizer`. You can read
about it in the `phug documentation`_:

    The Optimizer is a tool that avoids loading the Phug engine if a file is
    available in the cache. On the other hand, it does not allow to change the
    adapter or user post-render events.

Wordless supports enabling this important optimization by setting an
environment variable (in any way your system supports it) or a global
constant to be defined in ``wp-config.php``. Let's see this Wordless
internal code snippet:

.. literalinclude:: /../../wordless/helpers/render_helper.php
    :lines: 66-82
    :language: php
    :caption: render_helper.php

where we search for ``ENVIRONMENT`` and thus we'll activate PHUG's
``Optimizer`` if the value is either ``production`` or ``staging``.

.. note::
    Arbitrary values are not supported.

The simplest approach is to to define a constant inside ``wp-config.php``.

.. code-block:: php
    :caption: wp-config.php

    <?php
    // [...]
    define('ENVIRONMENT', 'production');
    // [...]

Deploy
######

Wordless is agnostic about the deploy strategy. Our favourite product for
deploying WordPress is `Wordmove`_.



.. _Wordmove: https://github.com/welaika/wordmove
.. _production mode: https://webpack.js.org/concepts/mode/
.. [#f1] https://docs.npmjs.com/files/package.json#scripts
.. _phug documentation: https://phug-lang.com/#usage

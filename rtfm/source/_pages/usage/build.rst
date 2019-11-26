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
    :ref:`CoffeeScript and Sass`

We offer standard approaches for both environments. They are handled -
as expected - through ``package.json`` 's scripts [#f1]_:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :lines: 13-24
    :language: javascript
    :caption: package.json

It is expected - but it's still up to you - that before every build you will
clean the compiled files. ``yarn clean:dist`` will do the cleanup.

**Build for development**

.. code-block:: bash

    yarn clean:dist && yarn build:dev

**Build for production**

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

    You can easily disable this behaviour setting ``devtool: false`` in ``webpack.env.coffee``
    inside the ``prodOptions`` object.

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
    define('ENVIRONMENT', 'production');

Deploy
######

Wordless is agnostic about the deploy strategy. Our favourite product for
deploying WordPress is `Wordmove`_.



.. _Wordmove: https://github.com/welaika/wordmove
.. _production mode: https://webpack.js.org/concepts/mode/
.. [#f1] https://docs.npmjs.com/files/package.json#scripts
.. _phug documentation: https://phug-lang.com/#usage

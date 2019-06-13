.. _Build:

Build and distribution
======================

Since Wordless uses Webpack, we have to manage build and distribution
strategies for dev and staging/production.

The most widespread folder naming approach to distinguish between source
and built code are ``src`` and ``dst``, but Wordless has different naming
due to its backward compatibility effort.

The source asset code is placed in
``theme/assets/{javascripts|stylesheets|images}``, while built/optimized code
is placed - automatically by Webpack - in
``assets/{javascripts|stylesheets|images}``

.. seealso::
    :ref:`CoffeeScript and Sass`

We offer standard approaches for both environments. They are handled -
as expected - through ``package.json`` 's scripts [#f1]_:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :lines: 9-17
    :language: javascript
    :caption: package.json
    :emphasize-lines: 3,4,8

It is expected - but it's still up to you - that before every build you will
clean the compiled files.

**Build for development**

.. code-block:: bash

    yarn clean:dist && yarn build:dev

**Build for production**

.. code-block:: bash

    yarn clean:dist && yarn build:prod

Production build will essentially:

* enable Webpack's `production mode`_
* do not produce source maps
* do minimize assets

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
    :lines: 81-87,89-91
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

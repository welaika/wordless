.. _BuildProd:

Production build
================

.. code-block:: bash

    yarn clean:dist && yarn build:prod

Production build will essentially:

* enable Webpack's `production mode`_
* do not produce source maps for CSS
* do minimize assets

.. note::
    By default the production build won't produce source-maps for JS.

    You can easily change this behaviour updating ``const needSourceMap = (env.DEBUG == 'true');``
    to ``const needSourceMap = true;`` in ``webpack.env.js``

.. _production mode: https://webpack.js.org/concepts/mode/

Release signature
#################

You notice that ``build:prod`` script will invoke ``sign-release`` too.
The latter will write the SHA of the current GiT commit into the
``release.txt`` file in the root of the theme.

You can easily disable this behaviour if you'd like to.

``release.txt`` is implemented to have a reference of the code version deployed
in production and to integrate external services that should requires release
versioning (for us in Sentry).

.. _PHUGoptimizer:

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
    :lines: 104-111
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

.. _phug documentation: https://phug-lang.com/#usage

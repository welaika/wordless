.. _Build:

Build and distribution
======================

Since Wordless is using Webpack, we have to manage build and distribution
strategies for dev and staging/production.

Most spread folder naming to distinguish between source and built code are
``src`` and ``dst``, but Wordless has different naming due to backword
compatibility effort.

Source assets' code is placed in
``theme/assets/{javascripts|stylesheets|images}``, while built/optimized code
is placed - automatically by Webpack - in
``assets/{javascripts|stylesheets|images}``

.. seealso::
    :ref:`CoffeeScript and Sass`

We offer standard approarches for both the environments. They are handled -
as expected - through ``package.json`` 's scripts [#f1]_:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :lines: 9-17
    :language: javascript
    :caption: package.json
    :emphasize-lines: 3,4,8

It is expected - but up to you - that before every build you will clean
compiled files

**Build for development**

.. code-block:: bash

    yarn clean:dist && yarn build:dev

**Build for production**

.. code-block:: bash

    yarn clean:dist && yarn build:prod

Production build will essentially

* enable Webpack's `production mode`_
* do not produce source maps
* do minimize assets

**Deploy**

Wordless is agnostic about deploy strategy. Our favourite product to deploy
WordPress is `Wordmove`_.



.. _Wordmove: https://github.com/welaika/wordmove
.. _production mode: https://webpack.js.org/concepts/mode/
.. [#f1] https://docs.npmjs.com/files/package.json#scripts

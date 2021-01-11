.. _Build:

Intro
=====

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

.. [#f1] https://docs.npmjs.com/files/package.json#scripts

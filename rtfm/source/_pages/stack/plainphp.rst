.. _PlainPhp:

Using plain PHP templates
=========================

.. todo::
    Move :ref:`PlainPhp` into :ref:`RenderingPhp`

Let's take the unaltered default theme as an example. In ``views/layouts`` we
have the ``default`` template which calls a ``include`` for the
``header.pug`` partial.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/layouts/default.pug
    :language: slim
    :caption: views/layouts/default.pug
    :emphasize-lines: 8

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/layouts/header.pug
    :language: slim
    :caption: views/layouts/header.pug

Let's suppose we need to change ``header.pug`` in a PHP template because we don't
like PUG or we need to write complex code there.

.. warning::
    If you have to write complex code in a view you are on the wrong path :)

#. Rename ``header.pug`` in ``_header.php``

#. Update its content, e.g.:

    .. code-block:: php
        :caption: views/layouts/_header.php

        <h1> <?php echo link_to(get_bloginfo('name'), get_bloginfo('url')); ?> </h1>
        <h2> <?php echo htmlentities(get_bloginfo('description')) ?> </h2>

#. In ``default.pug`` substitute PUG's ``include /layouts/header.pug`` with the appropriate Wordless' PHP render helper

    .. code-block:: pug
        :caption: views/layouts/default.pug

        = render_partial('layouts/header')

#. Done

When ``render_partial("layouts/header")`` doesn't find ``_header.html.pug`` it
will automatically search for ``_header.html.php`` and will use it *as is*,
directly including the PHP file.

Take away notes
###############

* PUG (and thus PHUG) has some builtin function to compose templates: ``include``, ``extends``, ``mixin``.
Such function works only within and for PUG files. That's why in order to load a plain PHP template
we need to use plain PHP functions.

Conclusions
###########

As you can see, Wordless does not force you that much. Moreover, you will continue
to have its goodies/helpers to break down views in little partials, simplifying
code readability and organization.

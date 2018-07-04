.. _PlainPhp:

Using plain PHP templates
=========================

Let's take the unaltered default theme as an example. In ``views/layouts`` we
have the ``default`` template which calls a ``render_partial`` for the
``_header`` partial.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/theme/views/layouts/default.html.pug
    :language: slim
    :caption: theme/views/layouts/default.html.pug
    :emphasize-lines: 6

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/theme/views/layouts/_header.html.pug
    :language: slim
    :caption: theme/views/layouts/_header.html.pug

Let's suppose we need to change ``_header`` in a PHP template because we don't
like PUG or we need to write complex code there.

.. warning::
    If you have to write complex code in a view you are on the wrong path :)

#. Rename ``_header.html.pug`` in ``_header.html.php``

#. Update it's content, e.g.:

    .. code-block:: php
        :caption: theme/views/layouts/_header.html.php

        <h1> <?php echo link_to(get_bloginfo('name'), get_bloginfo('url')); ?> </h1>
        <h2> <?php echo htmlentities(get_bloginfo('description')) ?> </h2>

#. Done

When ``render_partial("layouts/header")`` won't find ``_header.html.pug`` it
will automatically search for ``_header.html.php`` and will use it *as is*,
without passing through any compilation process.

Conclusions
###########

As you can see, Wordless do not force you so much. Moreover you will continue
to have its goodies/helpers to break down views in little partials, simplifying
code readability and organization.

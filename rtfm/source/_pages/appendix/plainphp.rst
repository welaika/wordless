.. _PlainPhp:

Using plain PHP templates
=========================

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

#. In ``default.pug`` substitute the line ``include /layouts/header.pug`` with the appropriate
   Wordless' PHP render helper

    .. code-block:: pug
        :caption: views/layouts/default.pug

        = render_partial('layouts/header')

#. Done

When ``render_partial("layouts/header")`` doesn't find ``_header.pug`` it
will automatically search for ``_header.php`` and will use it *as is*,
directly including the PHP file.

.. note::
    ``render_partial`` function expects that partials are named with a leading underscore (``_``).
    This is due to backward compatibility and we have to stuck and deal with it.

.. note::
    PUG (and thus PHUG) has some builtin function to compose templates: ``include``, ``extends``, ``mixin``.
    Such functions work only within and for PUG files. That's why in order to load a plain PHP template
    we need to use plain PHP functions.

.. _RenderingPhp:
PHP render helpers
##################

render_view()
"""""""""""""

The main helper function used to render a view is ``render_view()``. Here is its signature:

.. code-block:: php

    <?php
    /**
        * Renders a view. Views are rendered based on the routing.
        *   They will show a template and a yielded content based
        *   on the page requested by the user.
        *
        * @param  string $name   Filename with path relative to theme/views
        * @param  string $layout The template to use to render the view
        * @param  array  $locals An associative array. Keys will be variable
        *                        names and values will be variable values inside
        *                        the view
        */
        function render_view($name, $layout = 'default', $locals = array()) 	{
          /* [...] */
        }

.. note::
    Extension for ``$name`` can always be omitted.

Inside the ``views`` folder you can scaffold as you wish, but you'll have
to always pass the relative path to the render function:

.. code-block:: php

    <?php
    render_view('folder1/folder2/myview')

.. note::
    By the way vanilla theme ships with a proposed scaffold.

The ``$locals`` array will be auto-``extract()``-ed inside the required view, so you can do

.. code-block:: php

    <?php
    render_view('folder1/folder2/myview', 'default', array('title' => 'My title'))

and inside ``views/folder1/folder2/myview.pug``

.. code-block:: jade

    h1= $title

and ``$title`` variable will be set.


render_partial()
""""""""""""""""

``render_partial()`` is almost the same as its sister ``render_view()``, but it does
not accept a layout as argument. Here is its signature:

.. code-block:: php

    <?php
    /**
    * Renders a partial: those views followed by an underscore
    *   by convention. Partials are inside theme/views.
    *
    * @param  string $name   The partial filenames (those starting
    *                        with an underscore by convention)
    *
    * @param  array  $locals An associative array. Keys will be variables'
    *                        names and values will be variable values inside
    *                        the partial
    */
    function render_partial($name, $locals = array()) {

Partial templates – usually just called **“partials”** – are another device for
breaking the rendering process into more manageable chunks.

.. note::
    Partials files are **named with a leading underscore** to distinguish them
    from regular views, even though they are **referred to without the underscore**.

Layouts
"""""""

  ``views/layouts`` directory

When Wordless renders a view, it does so by combining the view within a layout.

E.g. calling

.. code-block:: php

    render_view('folder1/folder2/myview')

will be the same as calling

.. code-block:: php

    render_view('folder1/folder2/myview', 'default', array())

so that the ``default.pug`` (or ``.php`` if you'll update it too) layout will be rendered.
Within the layout,
you have access to the ``wl_yield()`` helper, which will combine the required
view inside the layout when it is called:

.. code-block:: pug

    doctype html
    html
      head= render_partial("layouts/head")
      body
        .page-wrapper
          header.site-header= render_partial("layouts/header")
          section.site-content= wl_yield()
          footer.site-footer= render_partial("layouts/footer")
        - wp_footer()

.. note::
    For content that is shared among all pages in your application that use the
    same layout, you can use partials directly inside layouts.

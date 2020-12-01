.. _RenderingPhp:

Rendering plain PHP
===================

.. todo::
    This is considered a fallback/second/legacy way of rendering. Move this chapter
    outside the **Usage** to some sort of "Extra" section. This should lighten the
    main doc section.
    There is also a :ref:`PlainPHP` section already...take a look

render_view()
"""""""""""""

The main helper function used to render a view is - fantasy name - ``render_view()``. Here is its signature:

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

Thanks to this helper, Wordless will always intercept **PUG** files and
automatically translate them to HTML.

.. note::
    Extension for ``$name`` can always be omitted.

.. seealso::
    PHUG section @ :ref:`CompileStack`

Inside the ``views`` folder you can scaffold as you wish, but you'll have
to always pass the relative path to the render function:

.. code-block:: php

    <?php
    render_view('folder1/folder2/myview')


The ``$locals`` array will be auto-``extract()``-ed inside the required view, so you can do

.. code-block:: php

    <?php
    render_view('folder1/folder2/myview', 'default', array('title' => 'My title'))

and inside ``views/folder1/folder2/myview.pug``

.. code-block:: jade

    h1= $title


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
        $parts = preg_split("/\//", $name);
        if (!preg_match("/^_/", $parts[sizeof($parts)-1])) {
            $parts[sizeof($parts)-1] = "_" . $parts[sizeof($parts)-1];
        }
        render_template(implode($parts, "/"), $locals);
    }

Partial templates – usually just called **“partials”** – are another device for
breaking the rendering process into more manageable chunks.

.. note::
    Partials files are **named with a leading underscore** to distinguish them
    from regular views, even though they are
    **referred to without the underscore**.

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

so that the ``default.html.phug`` layout will be rendered. Within the layout,
you have access to the ``wl_yield()`` helper, which will combine the required
view inside the layout when it is called:

.. code-block:: jade

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

Views
"""""

  ``views/**/*.pug`` or ``views/**/*.php``

This is the directory where you'll find yourself coding most of the time.
Here you can create a view for each main page of your theme, using Pug syntax
or plain HTML.

Feel free to create subdirectories to group together the files. Here's what
could be an example for the typical `WordPress loop`_ in an archive page:

.. _WordPress loop: http://codex.wordpress.org/The_Loop

.. code-block:: jade

    // views/posts/archive.html.pug
    h2 Blog archive
    ul.blog_archive
      while have_posts()
        - the_post()
        li.post= render_partial("posts/single")

.. code-block:: jade

    // views/posts/_single.html.pug
    h3!= link_to(get_the_title(), get_permalink())
    .content= get_the_filtered_content()

Wordless uses `Pug.php`_ - formerly called Jade.php - for your Pug views, a
great PHP port of the `PugJS`_ templating language. In this little snippet,
please note the following:

* The view is delegating some rendering work to a partial called
  ``_single.html.pug``

* There's no layout here, just content: the layout of the page is stored in a
  secondary file, placed in the ``views/layouts`` directory, as mentioned
  in the paragraph above

* We are already using two of the 40+ Wordless helper functions, ``link_to()``
  and ``get_the_filtered_content()``, to DRY up this view

* Because the ``link_to`` helper will return html code, we used
  `unescaped buffered code`_ to print PUG's function: ``!=``. Otherwise we'd
  have obtained escaped html tags.

It looks awesome, right?

.. _Pug.php: https://github.com/pug-php/pug
.. _PugJS: https://pugjs.org/api/getting-started.html
.. _unescaped buffered code: https://pugjs.org/language/code.html#unescaped-buffered-code

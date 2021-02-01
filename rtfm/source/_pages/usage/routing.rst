.. _Routing:

Routing
=======

**index.php** file in theme's root serves as a router to all the theme views.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/index.php
    :language: php
    :caption: index.php
    :lineno-start: 21
    :lines: 21-29

As you can see, you first determine the type of the page using `WordPress conditional tags`_, and then delegate the rendering to an individual view.

While ``index.php`` is the entry point of any WordPress theme, as it is called/required by it,
the ``render_template()`` function is where we connect WordPress core with Wordless' powerups.

The next chapter is all about rendering.

.. seealso::
    Using `Page Template Wordpress' feature`_ inside Wordless

.. _WordPress conditional tags : http://codex.wordpress.org/Conditional_Tags
.. _`Page Template Wordpress' feature`: https://github.com/welaika/wordless/wiki/Use-Page-Template-feature

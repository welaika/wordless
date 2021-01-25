.. _RenderStatic:

Static rendering
================

Static rendering is a built-in feature shipped since Wordless 5. It allows you to statically
compile a template into HTML and serve it. Successive rendering requests will directly serve
the static HTML if present.

You can compile any template into static HTML simply by using the ``render_static()`` function
in place of any ``render_template()`` PHP function or any PUG's ``include`` into you views.

This way you have control on having a completely static template or just some partial contents; so
you can isolate and make static a specific partial with heavy queries, or the whole page.

This is the definition of ``render_static()`` function:

.. literalinclude:: /../../wordless/helpers/render_helper.php
    :language: php
    :caption: render_helper.php
    :lineno-start: 163
    :lines: 163-170

.. warning::
    Using static rendering could lead to undesired effects by design (not specifically with Wordless).
    Be sure to know what you're doing. It's not alwas just a matter to be faster.

Static template example
#######################

Given this into ``index.php``

    .. code-block:: php

        if (is_front_page()){
          render_static("templates/static");
        }

and given this ``views/templates/static.pug``

    .. code-block:: pug

        extends /layouts/default.pug

        block yield
          h2 Archive (static example)

          ul.archive
            while (have_posts())
              - the_post()

              li
                include /partials/post.pug

visiting your home page will produce a static HTML into theme's ``tmp/`` dir similar to
``static.8739602554c7f3241958e3cc9b57fdecb474d508.html`` (template name + sha + extension).

The first time the template will be evaluated and compiled. Reloading the page the HTML will be
served without re-compiling.

Static partial example
######################

Given this into ``index.php``

    .. code-block:: php

        if (is_front_page()){
          render_template("templates/archive");
        }

and given this ``views/templates/static.pug``

    .. code-block:: pug

        extends /layouts/default.pug

        block yield
          h2 Archive (static example)

          ul.archive
            while (have_posts())
              - the_post()

              li
                - render_static('partials/post')

visiting your home page will produce a static HTML into theme's ``tmp/`` dir similar to
``post.8739602554c7f3241958e3cc9b57fdecb474d508.html`` (template name + sha + extension).

Invalidating the cache
######################

You have 3 way to handle this:

* manually deleting one or more ``.html`` files from theme's ``tmp/`` folder
* blank ``tmp/`` folder with ``wp wordless theme clear_tmp``
* from the "Cache management" menu within the admin panel

The "Cache management" menu needs to be activated decommenting this line

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/config/initializers/backend.php
    :language: php
    :caption: backend.php
    :lineno-start: 85
    :lines: 85-91
    :emphasize-lines: 7

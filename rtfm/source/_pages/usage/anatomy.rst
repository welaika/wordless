.. _Anatomy:

Theme anatomy
=============

This is a typical `Wordless theme directory structure`_:
::

  your_theme_dir
  ├── config/
  │   ├── initializers/
  │   └── locales/
  ├── dist/
  │   ├── fonts/
  │   ├── images/
  │   ├── javascripts/
  │   ├── stylesheets/
  │   └── README.md
  ├── helpers/
  │   └── README.mdown
  ├── node_modules/
  ├── src/
  │   ├── images/
  │   ├── javascripts/
  │   ├── stylesheets/
  │   └── main.js
  ├── tmp
  │   └── .gitkeep
  ├── views
  │   ├── layouts
  │   └── posts
  ├── .browserslistrc
  ├── .env
  ├── .gitignore
  ├── .nvmrc
  ├── .stylelintignore
  ├── .stylelintrc.json
  ├── Procfile
  ├── index.php
  ├── package.json
  ├── release.txt
  ├── screenshot.png
  ├── style.css
  ├── webpack.config.js
  ├── webpack.env.js
  └── yarn.lock

.. _Wordless theme directory structure : https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme

Now let's see in detail what is the purpose of all those directories.

Routing
#######

The `index.php` serves as a router to all the theme views.

.. code-block:: php

    <?php

    if (is_front_page()) {
      render_view("static/homepage)");
    } else if (is_post_type_archive("portfolio_work")) {
      render_view("portfolio/index");
    } else if (is_post_type("portfolio_work")) {
      render_view("portfolio/show");
    }

As you can see, you first determine the type of the page using `WordPress conditional tags`_, and then delegate the rendering to an individual view.

.. seealso::
    `render_view()`_ helper documentation

.. seealso::
    Using `Page Template Wordpress' feature`_ inside Wordless

.. _WordPress conditional tags : http://codex.wordpress.org/Conditional_Tags
.. _render_view(): http://welaika.github.io/wordless/docs/0.5/df/da0/classRenderHelper.html#aba4ec297d5c04d090f9b50bd0c1ba8d4
.. _`Page Template Wordpress' feature`: https://github.com/welaika/wordless/wiki/Use-Page-Template-feature

Rendering
#########

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

Helpers
#######

  ``helpers/*.php`` files

Helpers are basically small functions that can be called in your views to help
keep your code stay DRY. Create as many helper files and functions as you want
and put them in this directory: they will all be required within your views,
together with the `default Wordless helpers`_. These are just a small subset of
all the 40+ tested and documented helpers Wordless gives you for free:

.. _default Wordless helpers: http://welaika.github.io/wordless/docs/0.3/d3/de0/group__helperclass.html

- ``lorem()`` - A "lorem ipsum" text and HTML generator
- ``pluralize()`` - Attempts to pluralize words
- ``truncate()``- Truncates a given text after a given length
- ``new_post_type()`` and ``new_taxonomy()`` - Help you create custom posts and
  taxonomy
- ``distance_of_time_in_words()`` - Reports the approximate distance in time
  between two dates

Our favourite convention for writing custom helpers is to write 1 file per
function and naming both the same way. It will be easier to find with ```cmd+p``
😉

Initializers
############

  ``config/initializers/*.php`` files

Remember the freaky ``functions.php`` file, the one where you would drop every
bit of code external to the theme views (custom post types, taxonomies,
wordpress filters, hooks, you name it?) That was just terrible, right?
Well, forget it.

Wordless lets you split your code into many modular initializer files, each
one with a specific target:
::

  config/initializers
  ├──── backend.php
  ├──── custom_gutenberg_acf_blocks.php
  ├──── custom_post_types.php
  ├──── default_hooks.php
  ├──── hooks.php
  ├──── login_template.php
  ├──── menus.php
  ├──── shortcodes.php
  ├──── thumbnail_sizes.php

- **backend**: remove backend components such as widgets, update messages, etc
- **custom_gutenbers_acf_blocks**: Wordless has built-in support to ACF/Gutenberg blocks. Read more
  at :ref:`Blocks`
- **custom_post_types**: well... if you need to manage taxonomies, this is the
  place to be
- **default_hooks**: these are used by wordless's default behaviours; tweak them
  only if you know what are you doing
- **hooks**: this is intended to be your custom hooks collector
- **menus**: register new WP nav_menus from here
- **shortcodes**: as it says
- **thumbnail_sizes**: if you need custom thumbnail sizes

These are just some file name examples: you can organize them the way you
prefer. Each file in this directory will be automatically required by Wordless.

Locale files
############

  ``config/locales`` directory

Just drop all of your theme's locale files in this directory. Wordless will take
care of calling `load_theme_textdomain()`_ for you.

.. _load_theme_textdomain(): http://codex.wordpress.org/Function_Reference/load_theme_textdomain

.. note::
    Due to the WordPress localization framework, you need to append our
    ``"wl"`` domain when using internationalization. For example, calling
    ``__("News")`` without specifying the domain *will not work*.

    You'll **have** to add the domain `"wl"` to make it work:
    ``__("News", "wl")``

Assets
######

The Fast Way
""""""""""""

- write your SCSS in ``src/stylesheets/screen.scss``
- write your JS in ``src/javascripts/application.js``

and all will automagically work! :)

I need to really understand
"""""""""""""""""""""""""""

Wordless has 2 different places where you want to put your assets (javascript,
css, images):

- Place all your custom, project related assets into ``src/*``
- Since you are backed by Webpack, you can use NPM (``node_modules``) to import new dependencies
  following a completely standard approach

Custom assets
^^^^^^^^^^^^^

They must be placed inside ``src/javascript/`` and
``src/stylesheets/`` and ``src/images/``.

They will be compiled and resulting compilation files will be moved in the corresponding
``assets/xxx`` folder.

Compilation, naming and other logic is fully handled by webpack.

Images will be optimized by `ImageminPlugin`_. The default setup already translates
``url`` s inside css/scss files in order to point to images in the
right folder.

.. _ImageminPlugin: https://www.npmjs.com/package/imagemin-webpack-plugin

Take a look to the default ``screen.scss`` and ``application.js`` to see
usage examples.

.. seealso::
    :ref:`CompileStack`

.. seealso::
    * `Official SCSS guide <https://sass-lang.com/guide>`_

node_modules
^^^^^^^^^^^^

You can use node modules just as any SO answer teaches you :)

Add any vendor library through `YARN`_ with

.. code-block:: bash

    yarn add slick-carousel

Then in your Javascript you can do

.. code-block:: js

    require('slick-carousel');

or if the library exports ES6 modules you can do

.. code-block:: js

    import { export1 } from "module-name";

and go on as usual.


.. _YARN: https://yarnpkg.com/en/

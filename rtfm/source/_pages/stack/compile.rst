.. _CompileStack:

Code compilation
================

First things first: **using "alternative" languages is not a constraint**.
Wordless's scaffolded theme uses the following languages by default:

* **PHUG** for views as an alternative to PHP+HTML
* **ES2015** transpiled to JS using Babel
* **SCSS** for CSS

You could decide to use *plain* languages, just by renaming (and rewriting)
your files.

Wordless functions which require filenames as arguments, such as

.. code-block:: php

  <?php

  render_partial("posts/post")

  // or

  javascript_url("application")

will always require extension-less names and they will find your files whatever
extension they have.

.. seealso::

    PHUG paragraph @ :ref:`PlainPhp`

Anyway we think that the default languages are **powerful, more productive,
more pleasant to read and to write**.

Add the fact that wordless will take care of all compilation tasks, giving you
focus on writing: we think this is a win-win scenario.

.. _CompilePug:

PHUG
####

Pug_ is a robust, elegant, feature-rich template engine for Node.js. Here we
use a terrific PHP port of the language: Phug_. You can find huge
documentation on the official site https://www.phug-lang.com/, where you can
also find a neat live playground (click on the "Try Phug" menu item).

It comes from the JS world, so most front-end programmers should be familiar
with it, but it is also very similar to other template languages such as SLIM
and HAML (old!)

We love it because it is concise, clear, tidy and clean.

.. code-block:: jade
    :caption: A snippet of a minimal WP template

    h2 Post Details
    - the_post()
    .post
      header
        h3!= link_to(get_the_title(), get_permalink())
      content!= get_the_content()

Certainly, becoming fluent in PUG usage could have a not-so-flat
learning curve,
but starting from the basics shuold be affordable and the reward is high.

Who compiles PUG?
"""""""""""""""""

When a ``.pug`` template is loaded, the wordless plugin will automatically
compile (and cache) it. As far as you have the plugin activated you are ok.

.. important::

    By default, you have nothing to do to deploy in production, but if performance is
    crucial in your project, then you can optimize. See :ref:`PHUGoptimizer` for more informations.


.. _Pug: https://github.com/pugjs/pug
.. _Phug: https://github.com/pug-php/pug

.. _JS and SCSS:

JS and SCSS
###########

Here we are in the **Webpack** domain; from the compilation point of view there
is nothing Wordless-specific but the file path configuration.

Configuration is pretty standard, so it's up to you to read Webpack's
documentation. Let's see how paths are configured in ``webpack.config.js``.

Paths
"""""

Paths are based on the Wordless scaffold. Variables are defined at the top:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.js
    :language: js
    :caption: webpack.config.js
    :lineno-start: 2
    :lines: 2-5

and are used by the ``entry`` and ``output`` configurations:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.js
    :language: js
    :caption: webpack.config.js
    :lineno-start: 18
    :lines: 18-26

CSS will be extracted from the bundle by the standard mini-css-extract-plugin_

.. _mini-css-extract-plugin: https://webpack.js.org/plugins/extract-text-webpack-plugin/

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.js
    :language: js$
    :caption: webpack.config.js
    :lineno-start: 129
    :lines: 129-131

Inclusion of compiled files
"""""""""""""""""""""""""""

Wrapping up: the resulting files will be

* ``dist/javascripts/application.js``
* ``dist/stylesheets/screen.css``

As far as those files remain *as-is*, the theme will automatically load them.

If you want to edit names, you have to edit the WordPress
asset enqueue configurations:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/config/initializers/default_hooks.php
    :caption: config/initializers/default_hooks.php
    :language: php
    :linenos:
    :emphasize-lines: 6,16

.. note::
    The ``stylesheet_url`` and ``javascript_url`` Wordless' helpers
    will search for a file named as per the passed parameter inside the default
    paths, so if you use default paths and custom file naming, you'll be ok, but
    if you change the path you'll have to supply it using other WordPress
    functions.

.. seealso::
    `stylesheet_url signature`_

    `javascript_url signature`_

.. _stylesheet_url signature: http://welaika.github.io/wordless/docs/0.5/dd/d16/group__helperfunc.html#ga65c283fa91fd4801187737bf3f3b1e78
.. _javascript_url signature: http://welaika.github.io/wordless/docs/0.5/dd/d16/group__helperfunc.html#gaca881d0e89bddbab09b37219d8b2efd1

Multiple "entries"
""""""""""""""""""

"Entries" in the WebPack world means JS files (please, let me say that!).

Wordless is configured to produce a new bundle for each entry and by default
the only entry is ``main``

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/src/main.js
    :language: javascript
    :caption: main.js

As we've already said having an *entry* which requires both JS and SCSS,
will produce 2 separate files with the same name and different extension.

Add another *entry* and producing new bundles is as easy as

* create a new file

.. code-block::

    touch src/backend.js

* write something in it, should it be a ``require`` for a SCSS file or
  a piece of JS logic
* add the *entry* to webpack config

.. code-block:: js

    const entries = ['main', 'backend']

* include somewhere in your theme. For example in the WP's asset queue
  in ``default_hooks.php``

  .. code-block:: php

        function enqueue_stylesheets() {
            wp_register_style("main", stylesheet_url("main"), [], false, 'all');
            wp_register_style("backend", stylesheet_url("backend"), [], false, 'all');
            wp_enqueue_style("main");
            wp_enqueue_style("backend");
        }

        function enqueue_javascripts() {
            wp_enqueue_script("jquery");
            wp_register_script("main", javascript_url("main"), [], false, true);
            wp_register_script("backend", javascript_url("backend"), [], false, true);
            wp_enqueue_script("main");
            wp_enqueue_script("backend");
        }

  or add it anywhere in your templates:

  .. code-block:: jade

        header
            = stylesheet_link_tag('backend')
        footer
            = javascript_include_tag('backend')

Browserslist
""""""""""""

At theme's root you'll find the `.browserlistsrc`_ file.

By default it's used by Babel and Core-js3 to understand how to polifill
your ES2015 code. You can understand more about our default configuration
reading Babel docs at https://babeljs.io/docs/en/babel-preset-env#browserslist-integration

.. _.browserlistsrc: https://github.com/browserslist/browserslist

Stylelint
"""""""""

We use `Stylelint`_ to lint SCSS and to enforce some practices.
Nothing goes out of a standard setup. By the way some spotlights:

* configuration is in ``.stylelintrc.json`` file
* you have a blank ``.stylelintignore`` file if you may need
* ``yarn lint`` will launch the lint process
* if you use VS Code to write, we ship ``.vscode/settings.json`` in
  theme's root, which disables the built-in linters as per `stylelint plugin`_
  instructions. You may need to move those configurations based on the folder
  from which you start the editor.

.. _Stylelint: https://stylelint.io/
.. _stylelint plugin: https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint

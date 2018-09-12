.. _CompileStack:

Code compilation
================

First thing first: **using "alternative" languages is not a constrain**.
Wordless scaffolded theme uses the following languages by default:

* **PHUG** for views as alternative to PHP+HTML
* **CoffeeScript** 2 for JS (ES6 ready)
* **Sass** for CSS

You could decide to use *plain* languages, just by renaming (and rewriting)
your files.

Wordless' functions which want filenames as arguments such as

.. code-block:: php

  <?php

  render_partial("posts/post")

  // or

  javascript_url("application")

will always require extension-less names and they will find your files whatever
extension they have.

.. seealso::

    PHUG paragraph @ :ref:`PlainPhp`

Anyway we think that default languages are **powerful, more productive, more
pleasant to read and to write**.

Add the fact that wordless will take care of all compilation tasks, giving you
focus on writing: we think this is a win-win scenario.

PHUG
####

Pug_ is a robust, elegant, feature rich template engine for Node.js. Here we
use a terrific PHP port of the language: Phug_. You can find huge
documentation on the official site https://www.phug-lang.com/, where you can
also find a neat live playground (click on the "Try Phug" menu item).

It comes from the JS world, so most front-end programmers should be familiar
with it, but it is also very similar to other template languages such as SLIM
and HAML (old!).

We love it because it is concise, clear, tidy and clean.

.. code-block:: jade
    :caption: A snippet of a minimal WP template

    h2 Post Details
    - the_post()
    .post
      header
        h3!= link_to(get_the_title(), get_permalink())
      content!= get_the_content()

For sure becoming fluent in PUG usage could have a not-so-flat learning curve,
but starting from the basics shuold be affordable and the reward is high.

Who compiles PUG?
"""""""""""""""""

When a ``.html.pug`` template is loaded, Wordless plugin will automatically
compile (and cache) it. As far as you have the plugin activated you are ok.

.. important::

    You have to do nothing to deploy in production.


.. _Pug: https://github.com/pugjs/pug
.. _Phug: https://github.com/pug-php/pug

.. _CoffeeScript and Sass:

CoffeeScript and Sass
#####################

Here we are in the **Webpack** domain; from the compilation point of view there
is nothing Wordless specific but file path configuration.

The default webpack configuration file is written itself in coffeescript,
because it is `natively supported`_ by Webpack and because it make the code
more affordable to read.

.. _natively supported: https://webpack.js.org/configuration/configuration-languages/

Configuration is pretty standard, so it's up to you to read Webpack's
documentation. Let's see how paths are configured in ``webpack.config.coffee``.

Paths
"""""

Paths are based on the Wordless scaffold. Variables are defined at top:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.coffee
    :language: coffeescript
    :caption: webpack.config.coffee
    :lineno-start: 4
    :lines: 4-7

and are used by ``entry`` and ``output`` configurations:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.coffee
    :language: coffeescript
    :caption: webpack.config.coffee
    :lineno-start: 18
    :lines: 18-23

CSS will be extracted from the bundle by the usual extract-text-webpack-plugin_

.. _extract-text-webpack-plugin: https://webpack.js.org/plugins/extract-text-webpack-plugin/

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.coffee
    :language: coffeescript
    :caption: webpack.config.coffee
    :lineno-start: 69
    :lines: 69-82
    :emphasize-lines: 10

Compiled files inclusion
""""""""""""""""""""""""

Wrapping up: result files will be

* ``assets/javascripts/application.js``
* ``assets/stylesheets/screen.css``

As far as those files remain *as-is*, the theme will automatically load them.

If you want to edit names and/or paths, you have only to edit WordPress
assets enqueueing configurations:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/config/initializers/default_hooks.php
    :caption: config/initializers/default_hooks.php
    :language: php
    :linenos:
    :emphasize-lines: 6,16

.. note::
    ``stylesheet_url`` and ``javascript_url`` Wordless' helpers
    will search for a file named as per the passed parameter inside the default
    paths, so if you'll use default paths and custom file naming, you'll be ok, but
    if you'll change the path you'll have to supply it using other WordPress
    functinons.

.. seealso::
    `stylesheet_url signature`_

    `javascript_url signature`_

.. _stylesheet_url signature: http://welaika.github.io/wordless/docs/0.5/dd/d16/group__helperfunc.html#ga65c283fa91fd4801187737bf3f3b1e78
.. _javascript_url signature: http://welaika.github.io/wordless/docs/0.5/dd/d16/group__helperfunc.html#gaca881d0e89bddbab09b37219d8b2efd1

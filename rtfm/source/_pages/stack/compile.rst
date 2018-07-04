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

    :ref:`PlainPhp`

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

.. code-block:: slim
    :caption: A snippet of a minimal WP template

    h2 Post Details
    - the_post()
    .post
      header
        h3!= link_to(get_the_title(), get_permalink())
      content!= get_the_content()



Who compiles PUG?
"""""""""""""""""

When a ``.html.pug`` template is loaded, Wordless plugin will automatically
compile (and cache) it. As far as you have the plugin activated you are ok.

.. important::

    You have to do nothing to deploy in production.


.. _Pug: https://github.com/pugjs/pug
.. _Phug: https://github.com/pug-php/pug

CoffeeScript and Sass
#####################

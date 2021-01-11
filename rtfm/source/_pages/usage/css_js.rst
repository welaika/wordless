.. _Assets:

SCSS and JS
===========

The Fast Way
""""""""""""

- write your SCSS in ``src/stylesheets/screen.scss``
- write your JS in ``src/javascripts/application.js``

and all will automagically work! :)

A real explanation
""""""""""""""""""

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
``dist/xxx`` folder.

Compilation, naming and other logic is fully handled by webpack.

Images will be optimized by `image-minimizer-webpack-plugin`_. The default setup already translates
``url`` s inside css/scss files in order to point to images in the
right folder.

.. _image-minimizer-webpack-plugin: https://github.com/webpack-contrib/image-minimizer-webpack-plugin

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

Linters
"""""""

Wordless ships with preconfigured linting of SCSS
using `Stylelint`_.

It is configured in ``.stylelintrc.json``, you can add exclusion in
``.stylelintignore``; all is really standard.

The script ``yarn lint`` is preconfigured to run lint tasks.

.. tip::

    Code linting could be chained in a build script, e.g.:

    .. code-block::

        "build:prod": "yarn lint && webpack -p --bail --env.NODE_ENV=production"

.. tip::

    Code linting could be integrated inside a `Wordmove hook`_

.. tip::

    You can force linting on a pre-commit basis integrating Husky_
    in your workflow.


.. _Stylelint: https://stylelint.io/
.. _Wordmove hook: https://github.com/welaika/wordmove/wiki/Hooks
.. _Husky: https://github.com/typicode/husky

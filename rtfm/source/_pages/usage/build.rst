.. todo::
    Please find me a home and delete me

Code linting
############

Wordless ships with preconfigured linting of SCSS
using `Stylelint`_.

It is configured in ``.stylelintrc.json``, you can add exclusion in
``.stylelintignore``; all is really standard.

The script ``yarn lint`` is preconfigured to run the the lint tasks.

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

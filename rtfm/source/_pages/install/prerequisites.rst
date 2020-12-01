.. _Prerequisites:

Prerequisites
=============

#. Node. Depending on the Wordless version you'll need a specific Node version.
   Using NVM is recommended and the theme will be preconfigured with a
   ``.nvmrc`` file.
#. `WP-CLI`_ ``brew install wp-cli``
#. ``Yarn`` [1]_ globally installed: ``npm install -g yarn``
#. Test-related requirements (skip if you won't use the test suite)

    #. Composer [2]_ ``brew install composer``
    #. Selenium ``brew install selenium-server-standalone``
    #. Chrome Driver ``brew install chromedriver``

#. If you'd like to enable the mail-catcher while developing, install MailHog [3]_.
   On MacOS this is as simple as ``brew install mailhog``. Wordless
   will do the rest.

.. _official documentation: https://codex.wordpress.org/Installing_WordPress
.. _WP-CLI: http://wp-cli.org/#installing

.. seealso::
    :ref:`MailhogRef` for documentation about how to use MailHog in Wordless

.. seealso::
    :ref:`Node` for documentation about how to use nodejs in Wordless


.. [1] https://www.npmjs.com/package/yarn
.. [2] https://getcomposer.org/
.. [3] https://github.com/mailhog/MailHog

.. _ManualInstallation:

Manual installation
===================

At the end of the installation process you will have

* a plugin - almost invisible: no backend page, just custom ``wp-cli`` commands
* a theme - where you will do all of the work

Additional prerequisites
""""""""""""""""""""""""

#. WordPress installed and configured as per `official documentation`_

.. _official documentation: https://codex.wordpress.org/Installing_WordPress

.. note::
    We don't know if you have a local apache {M,L,W}AMPP instance or whatever
    in order to perform the official installation process. Keep in mind that
    Wordless' flow does not need any external web server, since it will use
    the `wp server`_ command to serve your wordpress.

.. _wp server: https://developer.wordpress.org/cli/commands/server/

.. seealso::
    :ref:`Server`

Steps
"""""

.. note::
    We consider that you have WordPress already up and running and you are in
    the project's root directory in your terminal.

#. Install and activate the wordpress plugin

    .. code-block:: bash

        wp plugin install --activate wordless

#. Scaffold a new theme

    .. code-block:: bash

        wp wordless theme create mybrandnewtheme

.. seealso::

    :ref:`WP-CLI plugin` for info about wp-cli integration

#. Enter theme directory

    .. code-block:: bash

        cd wp-content/themes/mybrandnewtheme

#. Setup all the things

    .. code-block:: bash

        yarn setup

#. Start the server - and the magic

    .. code-block:: bash

        yarn run server

Webpack, php server and your browser will automatically come up and serve
your needs :)

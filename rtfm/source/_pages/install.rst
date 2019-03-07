Installation
============

Wordless GEM (favourite)
########################

The quickest CLI tool to setup a new WordPress locally. Wordless ready.

No prerequisites. Just joy.

Navigate to https://github.com/welaika/wordless_gem to discover the tool and
set up all you need for local development. In less than 2 minutes ;)

(Not so) Manual
###############

At the end of the installation process you will have

* a plugin - almost invisible: no backend page, just ``wp-cli`` commands
* a theme - where we will do all of the work

Prerequisites
"""""""""""""

#. Install WP-CLI http://wp-cli.org/#installing
#. Install global packages from NPM: ``npm install -g foreman yarn`` [1]_ [2]_
   (you already have node on your development machine, haven't you?)
#. WordPress installed and configured as per `official documentation`_
#. Install MailHog_. On MacOS this is as simple
   as ``brew install mailhog``. Wordless will do the rest.

.. _official documentation: https://codex.wordpress.org/Installing_WordPress
.. _MailHog: https://github.com/mailhog/MailHog

.. note::
    We don't know if you have a local apache {M,L,W}AMPP instance or whatever
    in order to perform the official installation process. Keep in mind that
    Wordless's flow does not need any external web server, since it will use
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

#. Enter theme directory

    .. code-block:: bash

        cd wp-content/themes/mybrandnewtheme

#. Bundle NPM packages

    .. code-block:: bash

        yarn install

#. Start the server - and the magic

    .. code-block:: bash

        yarn run server

Webpack, php server and your browser will automatically come up and serve
your needs :)

.. seealso::
    :ref:`Server`

.. note::
    It is possible that your OS asks you to allow connections on server
    ports (3000 and/or 8080). It's just ok to do it.

.. [1] https://www.npmjs.com/package/yarn
.. [2] https://www.npmjs.com/package/foreman

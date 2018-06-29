.. _Server:

How is my site served?
======================

Said that with a

.. code-block:: bash

    yarn run server

you should be up and running, let's see in depth what happens behind
the scenes.

_________

YARN
####

``yarn run`` will search for a ``scripts`` section inside your ``package.json``
file and will execute the matched script.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/package.json
    :lines: 9-11
    :language: javascript
    :caption: package.json

Our default config will run ``nf start``, where ``nf`` is the Node Foreman
executable.


Foreman
#######

`Node Foreman`_ (``nf``) could do complex things, but Wordless uses it just
to be able to launch multiple processes when ``server`` is fired.

.. _Node Foreman: https://www.npmjs.com/package/foreman

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/Procfile
    :caption: Procfile

As you can see each line has a simple named command. Each command will be
launched and *foreman* will:

* run all the listed processes
* collect all STDOUTs from processes and print theme as one - with fancyness
* when stopped (CTRL-C) it will stop all of the processes

wp server
#########

Launched by ``nf``. Is a default *WP-CLI* command.

We are invoking it within a theme directory, but it will climb up directories
until it will find a ``wp-config.php`` file, then it will start a PHP server
on its default port (8080) and on ``127.0.0.1`` address as per our config.

.. note::
    You can directly reach ``http://127.0.0.1:8080`` in you browser in order
    to reach wordpress bypassing all the webpack *things* we're gonna show
    below.

BrowserSync
###########

The only relevant **Webpack**'s part in this section is the one about
BrowserSync_. It will start a web server at address ``127.0.0.1`` on port 3000.
This is where your browser will automatically go once launched.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/webpack.config.coffee
    :lines: 76-87
    :emphasize-lines: 3-6
    :language: coffeescript
    :caption: webpack.config.coffee

As you can see from the configuration, web requests will be proxy-ed to the
underlying ``wp server``.

Since *BrowserSync* is invoked through a Webpack plugin
(`browser-sync-webpack-plugin`_) we will benefit from automatic browser
autoreloading when assets will be recompiled by Webpack self.

.. seealso::
    :ref:`CompileStack` for other Webpack default configurations

.. note::
    *BrowserSync*'s UI will be reachable at ``http://127.0.0.1:3001`` as per
    default configuration.

.. warning::
    If you will develop with WordPress backend in a tab, *BrowserSync* will
    ignorantly reload that tab also (all tabs opened on port 3000 actually).
    This could slow down your server. We advice to use WordPress backend
    using port 8080 and thus bypassing *BrowserSync*.

.. _BrowserSync: https://www.browsersync.io/
.. _browser-sync-webpack-plugin: https://www.npmjs.com/package/browser-sync-webpack-plugin

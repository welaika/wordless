.. _TestSuite:

Test Suite
==========

The default Wordless theme is shipped with preconfigured test suite.

The test suite is implemented using the awesome `WPBrowser`_ and thus `Codeception`_.

.. note::
  By default Wordless is configured to run **acceptance** (aka **integration** or **e2e** or **browser**) test suite alone. If you'd like to run *functional* or *unit* suites, you'll simply have to update the ``yarn test`` script accordingly in ``package.json`` file.

Quick start
###########

Add tests to the ``tests/acceptance/WPFirstCest.php`` file or write your own file in the same folder.

To run acceptance test suite you have to start the test server in one terminal

    .. code-block:: bash

        yarn test:server

and in another terminal let's actually run tests:

    .. code-block:: bash

        yarn test

While ``test`` will simply run **acceptance** test suite, ``test:server`` is a variant of the default ``server`` task which load different ``Procfile`` and ``.env`` files.

Where are test configurations?
##############################

* ``test/`` folder. This is where your test suites lay.

* PHP dependencies declared in ``composer.json`` file shipped within the theme. This will create a ``/vendor`` folder inside the theme whilist ``yarn setup`` task

* custom ``wp-config.php``. This will be helpful to autodymagically (automatically, dynamically, magically; just in case you were wondering 🙄) switch from development to test database whilist test suite execution

* 2 test related node scripts: ``yarn test:server`` and ``yarn test``. Obviously declared inside ``package.json``

* a test database on your local machine called ``$THEME_NAME_test`` (where ``$THEME_NAME`` is the chosen name during Wordless' installation process) is created whilist ``yarn setup`` task

* *ad hoc* ``Procfile.testing``, ``.env.testing`` and ``.env.ci``

* ready-to-go ``.gitlab-ci.yml`` file into the project root

.. note::
    ``vendor/`` folders are ignored in ``.gitignore`` by default

.. _WPBrowser: https://wpbrowser.wptestkit.dev/
.. _Codeception: https://codeception.com/

How should I write tests?
#########################

This documentation is not intended to giude you thourgh testing concepts nor on Codeception's syntax. You can already find great documentation and I advice you to start from

* https://wpbrowser.wptestkit.dev/modules/wpwebdriver

* https://wpbrowser.wptestkit.dev/modules/wpbrowser

* https://wpbrowser.wptestkit.dev/modules/wpdb

where you will find Wordpress specific methods and links to base Codeception's methods all in one place.

Factory template
""""""""""""""""

The only thing Wordless actually adds to the default WPBrowser's setup is a ``FactoryHelper`` class, which is intended to create factory methods and which already integrates `Faker`_.

Take a look at its ``haveOnePost()`` method to understand the simple concept behind the factory.

.. _Faker: https://packagist.org/packages/fzaninotto/faker

CI
##

We ship default configuration for GitLab by putting a ``.gitlab-ci.yml`` file in you project's root folder.

That is configured to run out-of-the-box. And if you use other CI's products you can use it as a starting point for your own configuration and then delete it without any regard :)

Troubleshooting
###############

* **yarn setup -> Error: Error establishing a database connection.**

  Check your db’s username & password in the ``wp-config.php``

* **yarn test -> Db: SQLSTATE[HY000] [2054] The server requested authentication method unknown to the client while creating PDO connection**

  Check your db’s username & password in ``.env.testing``, inside the theme’s folder

* **yarn test -> Could not find, or could not parse, the original site URL; you can set the "originalUrl" parameter in the module configuration to skip this step and fix this error.**

  The command ``yarn test:db:snapshot`` can be useful.

* **yarn test -> [ConnectionException] Can't connect to Webdriver at http://localhost:4444/wd/hub. Please make sure that Selenium Server or PhantomJS is running.**

  Check if you are running ``yarn test:server`` in another terminal ☺️.

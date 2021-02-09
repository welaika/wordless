.. _WordlessGem:

Wordless gem
============

The quickest CLI tool to setup a new WordPress locally. Wordless ready.

Navigate to https://github.com/welaika/wordless_gem to discover the tool and
set up all you need for local development. In less than 2 minutes ;)

The quickstart, given the prerequisites, is:

    .. code-block:: bash

        gem install wordless
        cd MY_DEV_FOLDER
        wordless new THEME_NAME [—db-user=DB_USER —db-password=DB_PASSWORD]

When ``--db-user`` is omitted it will default to ``admin``, when ``db-password`` is omitted it will
default to a blank password.

If you already have a WordPress installation and just want to add
Wordless to it, read the following paragraph.

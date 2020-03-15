.. _WP-CLI plugin:

CLI
===

When a Wordless theme is activated and you are inside project's path,
you automatically get an *ad-hoc* WP-CLI plugin.

Typing ``wp help`` you'll notice a ``wordless`` subcommand.

.. code-block::
    :caption: wp help wordless

    NAME

        wp wordless

    SYNOPSIS

        wp wordless <command>

    SUBCOMMANDS

        theme      Manage wordless themes

.. code-block::
    :caption: wp help wordless theme

    NAME

        wp wordless theme

    DESCRIPTION

        Manage wordless themes

    SYNOPSIS

        wp wordless theme <command>

    SUBCOMMANDS

        clear_tmp      Clear theme's `tmp` folder
        create         Create a new Wordless theme
        upgrade        Upgrade current active Wordless theme.

All subcommands are self-documented, so you can simply use, e.g.:

.. code::

    wp help wordless theme upgrade

to get the documentation.

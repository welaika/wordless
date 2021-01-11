.. _Locales:

Locale files
============

  ``config/locales`` directory

Just drop all of your theme's locale files in this directory. Wordless will take
care of calling `load_theme_textdomain()`_ for you.

.. _load_theme_textdomain(): http://codex.wordpress.org/Function_Reference/load_theme_textdomain

.. note::
    Due to the WordPress localization framework, you need to append our
    ``"wl"`` domain when using internationalization. For example, calling
    ``__("News")`` without specifying the domain *will not work*.

    You'll **have** to add the domain `"wl"` to make it work:
    ``__("News", "wl")``

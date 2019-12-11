.. _Initializers:

Initializers
============

  ``config/initializers/*.php`` files

Remember the freaky ``functions.php`` file, the one where you would drop every
bit of code external to the theme views (custom post types, taxonomies,
wordpress filters, hooks, you name it?) That was just terrible, right?
Well, forget it.

Wordless lets you split your code into many modular initializer files, each
one with a specific target:
::

  config/initializers
  ├──── backend.php
  ├──── custom_gutenberg_acf_blocks.php
  ├──── custom_post_types.php
  ├──── default_hooks.php
  ├──── hooks.php
  ├──── login_template.php
  ├──── menus.php
  ├──── shortcodes.php
  ├──── thumbnail_sizes.php

- **backend**: remove backend components such as widgets, update messages, etc
- **custom_gutenbers_acf_blocks**: Wordless has built-in support to ACF/Gutenberg blocks. Read more
  at :ref:`Blocks`
- **custom_post_types**: well... if you need to manage taxonomies, this is the
  place to be
- **default_hooks**: these are used by wordless's default behaviours; tweak them
  only if you know what are you doing
- **login template**: utilities to customize the default WP login screen
- **hooks**: this is intended to be your custom hooks collector
- **menus**: register new WP nav_menus from here
- **shortcodes**: as it says
- **thumbnail_sizes**: if you need custom thumbnail sizes

These are just some file name examples: you can organize them the way you
prefer. Each file in this directory will be automatically required by Wordless.

Moreover: each of these files comes already packed with interesting, often used functions and
configurations. They are ready to be uncommented. Take youself a tour directly in the code
@ https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme/config/initializers

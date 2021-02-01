.. _Filters:

Filters
=======

The plugin exposes `WordPress filters`_ to let the developer
alter specific data.

.. _WordPress filters: https://codex.wordpress.org/Glossary#Filter

wordless_pug_configuration
##########################

.. literalinclude:: /../../wordless/helpers/pug/wordless_pug_options.php
    :emphasize-lines: 6
    :language: php
    :caption: wordless/helpers/pug/wordless_pug_options.php

**Usage example**

.. code-block:: php

    <?php
    add_filter('wordless_pug_configuration', 'custom_pug_options', 10, 1);

    function custom_pug_options(array $options): array {
        $options['expressionLanguage'] = 'js';

        return $options;
    }

.. _wordless_acf_gutenberg_blocks_views_path:

wordless_acf_gutenberg_blocks_views_path
########################################

.. literalinclude:: /../../wordless/helpers/acf_gutenberg_block_helper.php
    :emphasize-lines: 5
    :language: php
    :caption: wordless/helpers/acf_gutenberg_block_helper.php
    :lineno-start: 48
    :lines: 48-66

**Usage example**

.. code-block:: php

    <?php
    add_filter('wordless_acf_gutenberg_blocks_views_path', 'custom_blocks_path', 10, 1);

    function custom_blocks_path(string $path): string {
        return 'custom_path';
    }

This way Wordless will search for blocks' partials in
``views/custom_path/block_name.html.pug``
so you can use ``render_partial('custom_path/block_name')`` to render them in your
template.

The default path is ``blocks/``.

.. note::

    The path will be always relative to ``views/`` folder

Actions
=======

wordless_component_validation_exception
#######################################

.. literalinclude:: /../../wordless/helpers/component_helper.php
    :emphasize-lines: 6
    :language: php
    :caption: wordless/helpers/component_helper.php
    :lineno-start: 52
    :lines: 52-70

When an object of class ``Wordless\Component`` fails its validation, it will throw an exception
only if ``ENVIRONMENT`` **is not** ``production``. When in ``production`` nothing will happen, in
order to be unobstrusive and not breaking the site to your users. The developer will still see
specific excpetion happening.

You can customize the behaviour by adding your action as documented in the code.

What we like to do is to add here a notification to our Sentry account (thanks to
https://github.com/stayallive/wp-sentry/ plugin)

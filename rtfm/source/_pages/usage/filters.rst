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

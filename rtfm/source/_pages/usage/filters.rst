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

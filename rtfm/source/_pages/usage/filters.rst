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

wordless_tmp_dir_exists
##########################

.. literalinclude:: /../../wordless/helpers/render_helper.php
    :emphasize-lines: 255
    :language: php
    :caption: wordless/helpers/render_helper.php

**Usage example**

.. code-block:: php

    <?php
    function validateWordlessTmpDir( $tmpdir, $coreCheckResult ){

        if (false == $coreCheckResult) { // Just giving examples
            sendAlertToSysadmin();
        }

        $file_counts = 0;

        if ( file_exists( $tmpdir ) ) {
            $file_counts = preg_grep('/(.*).(php|txt)$/', scandir( $tmpdir ) );
        }

        return count( $file_counts ) > 0;
    }

    add_filter('wordless_tmp_dir_exists', 'validateWordlessTmpDir', 10, 2);

Sometimes tmp folder in theme directory, may not have write permission in dedicated server, Hence failure to load pug template from tmp.
In tmp directory, if there is compiled files listed following hook can be used to check file counts and override ensure_tmp_dir function to return true.
In some cases files can be compiled via command line to generate files in tmp dir.
here the filter code is added in ``themes/exmaple-theme/config/initializers/hooks.php``

wordless_environment
##########################

.. literalinclude:: /../../wordless/helpers/render_helper.php
    :emphasize-lines: 110
    :language: php
    :caption: wordless/helpers/render_helper.php

**Usage example**

.. code-block:: php

    <?php
        add_filter( 'wordless_environment' , function( $pug_environment ) {
	    $env = getCustomEnv(); // example a custom define global function
	    if ( $env->is_local() ) {
		    return $pug_environment;
	    }

	        return 'production';
        } );

Here the filter code is added in ``themes/exmaple-theme/config/initializers/hooks.php``

If there is different or custom environment name defined this hook can override it, rather than defaulting to development always. For example if environment is called UAT or SIT.

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

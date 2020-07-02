.. _Blocks:

ACF Gutenberg Blocks
====================

Worldess has built-in support for registering new custom gutenberg blocks.

To register a block go to the initializer
``config/initializers/custom_gutenberg_acf_blocks.php``
and uncomment the last line in the ``custom_gutenberg_acf_blocks()`` function.

The function is very well self documented:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/config/initializers/custom_gutenberg_acf_blocks.php
    :language: php
    :caption: config/initializers/custom_gutenberg_acf_blocks.php

Having a block registered this way, you will found it selectable
in the ACF field group's options.

Said you'll register a block like

.. code-block:: php

    <?php

    create_acf_block('slider', [
        'title' => 'Slider',
        'description' => 'Slider',
        'category' => 'widgets',
        'icon' => 'admin-comments',
        'render_callback' => '_acf_block_render_callback',
        'keywords' => [ 'image', 'slider' ]
    ]);

Wordless will search for two partials to render:

* ``views/blocks/admin/_slider.html.pug``
* ``views/blocks/_slider.html.pug``

The first one is used to render the block in the backend Gutenberg's
interface. If absent, then the second will be used.

You will be obviously free to render the block anywhere in your
front-end template, since it's a simple partial:

.. code-block:: jade

    render_partial('blocks/slider')

.. note::

    You can change the path where the partial is searched for
    by using the :ref:`wordless_acf_gutenberg_blocks_views_path`
    filter

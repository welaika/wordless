.. _RenderingPug:

Rendering PUG
=============

In Wordless templates are written in PUG. The plugin incorporates and loads for you the
excellent ``pug-php/pug`` library, that's a complete `PHP rewrite`_ of the `original javascript`_
PUG.

.. _original javascript: https://github.com/pugjs/pug
.. _PHP rewrite: https://www.phug-lang.com/

.. seealso::
    :ref:`CompilePug` section @ :ref:`CompileStack`

Rendering a view is mainly achieved through calling ``render_template()`` method inside
the ``index.php``, as mentioned in :ref:`Routing`.

The vanilla theme is shipped with an example scaffolding, but you can scaffold as you
wish, **as long as you retain the ``views/`` folder**.

This is the proposed scaffold for views:

::

    vanilla_theme/views/
    ├── components
    ├── layouts
    ├── partials
    └── templates

Layouts
"""""""

``layouts/`` is where to put the outer part of your templates; usually a layout represents the
  always repeated (non content) parts of your template such as ``<head>``, the main header, the
  navigation, the footer.

Vanilla theme ships this ``default.pug`` layout:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/layouts/default.pug
    :language: pug
    :caption: views/layouts/default.pug

Please, do ignore the function of the ``include`` keyword at the moment. Will exmplain it
in the "Partial" paragraph of this same chapter.

We can note what it brings in:

* doctype declaretion
* ``<html>`` tag
* ``<head>`` tag
* site header
* site content
* site footer

The most important thing to focus on now is the ``block yield``. This is where a **template** will
fill the layout with content. We're going to cover how this happens in the next paragraph. But
it's important to understand this concept: **a layout**, by convention, **is not meant to be
directly rendered**, instead each **template** is in charge to declare what layout it want to use.

Templates
"""""""""

Templates are what you will directly render.

The helper function used to render a template is ``render_template()`` and it is intended to be
used mainly into ``index.php`` file. Here is its signature:

.. literalinclude:: /../../wordless/helpers/render_helper.php
    :language: php
    :caption: render_helper.php
    :lineno-start: 61
    :lines: 61-76

For example:

.. code-block:: php

    <?php
    render_template('posts/single.pug')

will search for the PUG template ``views/posts/single.pug`` relative to the theme folder.

You can also pass an array of variables to your template, by setting the ``$locals`` parameter:

.. code-block:: php

    <?php
    render_template('posts/single.pug', ['foo' => 'bar'])

The ``$locals`` array will be auto-``extract()``-ed inside the required view, so you can
use them into the template. E.g: inside ``views/posts/single.pug``

.. code-block:: pug

    h1= $foo

Rendering a template for a webpage would involve to write a lot of boilerplate code (``<html>`` tag,
``<head>`` and so on). This is where our **layouts** come handy. Let's see how a simple template is
structured:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/templates/single.pug
    :language: pug
    :caption: view/templates/single.pug

* with ``extends /layouts/default.pug`` the template is declaring which layout it is going to extend
* ``block yield`` is the same declaration we found into ``views/templates/default.pug`` and it's
  telling to the chosen template: "Hey, template! You must inject all the below code into your
  *block* named ``yield``

The result, just to easily imagine it out, would be:

.. code-block:: pug

    doctype html
    html
      head
        include /layouts/head.pug
      body
        .page-wrapper
          header.site-header
            include /layouts/header.pug

          section.site-content
            h2 Post Details // This is where the layout declared `block yield`
            - the_post()
            include /partials/post.pug

          footer.site-footer
            include /layouts/footer.pug

        // jQuery and application.js is loaded by default with wp_footer() function. See config/initializers/default_hooks.php for details
        - wp_footer()

Obviously this composition is transparently handled by PUG.

So we have a structured template now; we're ready to undestand **partials** and how to use them
with the ``include`` keyword.

.. note::
    You will notice that ``extend`` and ``include`` argument always strarts with a trailing slash.
    This is the PUG convention to search for files into the ``views/`` folder, which is configured
    as the "root" search folder.

Partials
""""""""

Partials are optional but powerful; they're a tool for split your bigger into smaller
and more managable chunks.

They are included into parent files using the ``include`` keyword.

For example this template

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/templates/single.pug
    :language: pug
    :caption: view/templates/single.pug

having this partial

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/partials/post.pug
    :language: pug
    :caption: view/templates/single.pug

will produce

.. code-block:: pug

    doctype html
    html
      head
        include /layouts/head.pug
      body
        .page-wrapper
          header.site-header
            include /layouts/header.pug

          section.site-content
            h2 Post Details // This is where the layout declared `block yield`
            - the_post()
            post
              header
                h3!= link_to(get_the_title(), get_permalink())
              content!= get_the_filtered_content()

          footer.site-footer
            include /layouts/footer.pug

        // jQuery and application.js is loaded by default with wp_footer() function. See config/initializers/default_hooks.php for details
        - wp_footer()

Straight. The included partial will share the scope with the including template.

There is a specific type of partial supported by Wordless by default: **components**. We'll see
how to use them in the next paragraph.

Components
""""""""""

Components are a special flavour of partials that can receive scoped variables. **Components are
functions**: given the same parameters they will always render the same piece of HTML. This
way you can **re-use** a component in any place of your app, being sure to obtain the same output.

Let's see an example taken from the vanilla theme.

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/templates/archive.pug
    :language: pug
    :caption: view/templates/archive.pug

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/components/post.pug
    :language: pug
    :caption: view/components/post.pug

First news: in ``view/templates/archive.pug`` we have a top ``include``; this will not directly
produce anything, because ``views/components/post.pug`` contains only a ``mixin`` declaration.

The ``mixin`` is named ``post`` and takes 1 argument ``$component``. A mixin won't produce anything
when ``include``-ed, but only when invoked. You have really to think of it like a regular PHP
function.

By using ``include /components/post.pug`` we're now able to invoke the mixin using the syntax
``+`` + ``mixinName``, thus ``+post($arg1)`` which is our second and last news about components.

.. note::
    Wordless supports the ``component`` keyword as an alias to the default ``mixin`` keyword
    in PUG templates. This is much more expressive. The counter-effect is that your syntax
    highligter won't appreciate it that much :)

.. seealso::
    PHUG ``mixin`` documentation @ https://www.phug-lang.com/#mixins

Arguments validation
####################

In the previous example you've seen that the ``$component`` argument is an instance of
``ComponentPost`` class. Let's explain what and why it is.

.. note::
    When you write your mixins, you decide what and how many arguments they will
    require. Validators aren't mandatory, but a useful and poweful tool you're free to use or not.

Visual components, given they accept arguments, are strictly dependent on data passed to them
through arguments. This is true in any front-end development stack/scenario/framework.

Since in WordPress you have not *models* and since you'll often rely on custom fields to gather
and pass data from the front-end to the DB and vice versa, you have not a "core" way to ensure
that you're passing valid objects (or data-structures) to your components to be rendered.

See this example:

.. code-block:: pug

    mixin post($title)
      post
        header
          h3!= "My title is {$title}"

    +post('')

Will render an ``<h3>`` tag with ``My title is``. This is a trivial example, but receiving wrong
data in specific situations could entirely broke your component and thus your view. Not speaking about types.

Here comes into play ``\Wordless\Component`` class. You can see it in action in our vanilla theme:

.. literalinclude:: /../../wordless/theme_builder/vanilla_theme/views/templates/archive.pug
    :language: pug
    :caption: view/templates/archive.pug
    :emphasize-lines: 12-13

Where ``ComponentPost`` is a custom class extending ``\Wordless\Component``:

.. code-block:: php
    :caption: This is a simplified version of helpers/ComponentPost.php in vanilla theme

    <?php

    use Symfony\Component\Validator\Constraints as Assert;

    class ComponentPost extends \Wordless\Component {
        public $post;

        public static function loadValidatorMetadata($metadata)
        {
            $metadata->addPropertyConstraint('post', new Assert\Type(WP_Post::class));
        }
    }

We are using Symphony's Validator; it is already loaded and ready to use, so you can
write your component's classes implementing all the validations as per the detailed
documentation https://symfony.com/doc/current/reference/constraints.html.

This is how's intended to be used inside Wordless:


* define a class extending ``\Wordless\Component``
* declare as many public attributes as your component needs
* instance the object passing arguments as an associative array ``$component = new ComponentPost(['post' => get_post()])``
* each key will be automatically cheked to be declared as an attribute into the component
  and the corresponding attribute will be set to the corresponding value. You can pass
  arguments only if they are declared into the component class.
* into the component is mandatory to implement a ``loadValidatorMetadata`` public static
  function. Inside of it you will write your actual validations. This name was chosen in
  order to stick with official documentation's naming.
* ``$component`` will be validated at instantiation time, so you will have an error or a valid
  object. No doubts.
* passing ``$component`` as your mixin's argument, inside the mixin you will be able to get
  its properties as expected: ``$component->attribute``.

Revisiting our previous exaple:

.. code-block:: php

    <?php

    use Symfony\Component\Validator\Constraints as Assert;

    class ComponentPost extends \Wordless\Component {
        public $title;

        public static function loadValidatorMetadata($metadata)
        {
            $metadata->addPropertyConstraint('post', new Assert\Type('string'));
            $metadata->addPropertyConstraint('post', new Assert\NotBlank());
        }
    }

.. code-block:: pug

    mixin post($component)
      post
        header
          h3!= "My title is {$component->title}"

    - $component = new ComponentPost(['title' => ['My title']]) // Error: not a string
    - $component = new ComponentPost(['title' => '']) // Error: is empty
    - $component = new ComponentPost(['title' => '']) // Error: is empty
    - $component = new ComponentPost(['turtle' => 'My title']) // Error: "turtle" undeclared property
    +post($component)

When a validation error is thrown, an error will be rendered instead of the template. This is true if the ``ENVIRONMENT`` constant is not set to ``production``. If you've declared the
environment as production, nothing will happen by default.
You can implement your custom action for production using the ``wordless_component_validation_exception`` action. For more info head to :ref:`Filters`

.. _Helpers:

PHP Helpers
===========

  ``helpers/*.php`` files

Helpers are basically small functions that can be called in your views to help
keep your code stay DRY. Create as many helper files and functions as you want
and put them in this directory: they will all be required within your views,
together with the `default Wordless helpers`_. These are just a small subset of
all the 40+ tested and documented helpers Wordless gives you for free:

.. _default Wordless helpers: http://welaika.github.io/wordless/docs/0.3/d3/de0/group__helperclass.html

- ``lorem()`` - A "lorem ipsum" text and HTML generator
- ``pluralize()`` - Attempts to pluralize words
- ``truncate()``- Truncates a given text after a given length
- ``new_post_type()`` and ``new_taxonomy()`` - Help you create custom posts and
  taxonomy
- ``distance_of_time_in_words()`` - Reports the approximate distance in time
  between two dates

Our favourite convention for writing custom helpers is to write 1 file per
function and naming both the same way. It will be easier to find with ```cmd+p``
😉

**Where is my** ``functions.php`` **?**

In a Wordless theme the isn't a ``functions.php`` file. It was too ugly to us to support it.
You have simply to consider ``helpers/*.php`` files as the explosion of your old messy
``functions.php`` into smaller chunks. And since all the helpers you'll write will be autorequired,
defined functions will work exactly the same way you are used to.

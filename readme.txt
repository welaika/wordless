=== Plugin Name ===
Contributors: welaika, stefano.verna
Donate link: https://github.com/welaika/wordless
Tags: sass, compass, haml, rails
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 0.3

Wordless dramatically speeds up and enhances your custom themes
creation, thanks to Sass, Compass, Haml and Coffeescript.

== Description ==

Wordless is an opinionated WordPress plugin that dramatically speeds up and enhances your custom themes creation. Some of its features are:

* A structured, organized and clean [theme organization](https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme) (taken directly from Rails);
* Ability to create a new theme skeleton directly within the WordPress backend interface;
* Ability to write PHP code using the beautiful [Haml templating system](http://haml-lang.com/);
* Ability to write CSS stylesheets using the awesome [Sass syntax](sass-lang.com) and the [Compass framework](http://compass-style.org/);
* Ability to write [Coffeescript](http://jashkenas.github.com/coffee-script/) instead of the boring, oldish Javascript;
* A growing set of handy and documented helper functions ready to be used within your views;

You can always find the latest version of this plugin, as well as a
detailed README, on [Github](https://github.com/welaika/wordless).

== Installation ==

1. Your development machine needs a ruby environment, and the [compass](https://github.com/chriseppstein/compass), [sprockets](https://github.com/sstephenson/sprockets) and [coffee-script](https://github.com/josh/ruby-coffee-script) gem. See below to see how to setup WordPress on your machine using RVM;
2. The production machine doesn't need any extra-dependencies, as all the compiled assets automatically get statically backend by Wordless;
3. [Download the Wordless plugin](https://github.com/welaika/wordless/zipball/master), drop it in the `wp-content/plugins` directory and enable it from the WP "Plugins" section;
4. Enable the use of nice permalinks from the WP "Settings > Permalink" section. That is, we need the .htaccess file;
5. Create a brand new Wordless theme directly within the WP backend, from the WP "Appearance > New Wordless Theme" section;
6. Specify the path of your ruby executables, you can do it within the WP "Appearance > Wordless preferences" menu voice.


**RVM (recommended setup)**

It's recommended to use [RVM](http://rvm.beginrescueend.com) to handle ruby gems. Type the following from your terminal:

    rvm use 1.8.7
    rvm gemset create wordless
    rvm use 1.8.7@wordless
    gem install sprockets compass coffee-script
    rvm wrapper 1.8.7@wordless wordless compass ruby

Now you should be able to know the location of your RVM-wrapped ruby executables using `which wordless_ruby` and `which wordless_compass`. Write them down into the `config/initializers/wordpress_preferences.php` file.



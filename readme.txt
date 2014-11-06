=== Plugin Name ===
Contributors: welaika, stefano.verna
Donate link: https://github.com/welaika/wordless
Tags: sass, compass, haml, rails
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 0.5

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
3. Install the plugin from the WP plugins directory or drop the zip inside your wp-content/plugins directory.
4. Enable the use of nice permalinks from the WP "Settings > Permalink" section. That is, we need the .htaccess file;
5. Create a brand new Wordless theme directly within the WP backend, from the WP "Appearance > New Wordless Theme" section;
6. Specify the path of your ruby executables, you can do it within the WP "Appearance > Wordless preferences" menu voice.


**The RVM Way**

Our recommended way to get Ruby installed on your system is RVM, the Ruby Version Manager. This beautiful project creates a tight, clean and well organized Ruby platform in your system. Moreover it's deadly easy to setup (this requires git installed):

\curl -L https://get.rvm.io | bash -s stable --autolibs=enabled
Now reload your shell and install the latest Ruby version by running (this will take some time, go have a coffee or something):

rvm install 2.0.0
Whew, now you have a fully functional Ruby environment! The following oneliner will create a new gemset called wordless, install the gems wordless needs and create some handy symbolic links:

rvm use 2.0.0@wordless --create --default && \
gem install therubyracer sprockets compass coffee-script thor yui-compressor && \
rvm wrapper 2.0.0@wordless wordless compass ruby
Now you should be able to know the location of your RVM-wrapped ruby executables typing which wordless_ruby and which wordless_compass on your terminal.

== Changelog ==

0.5 Wordless had always used `yield()` function in its template; starting from some point yield() become a reseved PHP function, so we had to rename it in Wordless\' code. If you get errors search and replace *yield* inside the pugin directory with *wl_yield*. Sorry for the inconvenient.

== Upgrade Notice ==

WARNING! Version 0.5 is not backward compatible! please, read the change log carefully!!!

=== Plugin Name ===

Contributors: welaika, stefano.verna
Donate link: https://github.com/welaika/wordless
Tags: sass, compass, haml, rails, scss
Requires at least: 3.0
Tested up to: 4.1.1
Stable tag: 0.5.7
License: The MIT License
License URI: http://www.opensource.org/licenses/MIT

Wordless dramatically speeds up and enhances your custom themes creation, thanks to Sass, Compass, Haml and Coffeescript.

== Description ==

Wordless is an opinionated WordPress plugin that dramatically speeds up and enhances your custom themes creation. 

Some of its features are:

* A structured, organized and clean [theme organization](https://github.com/welaika/wordless/tree/master/wordless/theme_builder/vanilla_theme) (taken directly from Rails);
* Ability to create a new theme skeleton directly within the WordPress backend interface;
* Ability to write PHP code using the beautiful [Haml templating system](http://haml-lang.com/);
* Ability to write CSS stylesheets using the awesome [Sass syntax](sass-lang.com) and the [Compass framework](http://compass-style.org/);
* Ability to write [Coffeescript](http://jashkenas.github.com/coffee-script/) instead of the boring, oldish Javascript;
* A growing set of handy and documented helper functions ready to be used within your views;

You can always find the latest version of this plugin, as well as a
detailed README, on [Github](https://github.com/welaika/wordless).

== Installation ==

1. Your development computer needs [ruby](https://www.ruby-lang.org), [compass](https://github.com/chriseppstein/compass), [sprockets](https://github.com/sstephenson/sprockets) and [coffee-script](https://github.com/josh/ruby-coffee-script). Please follow [this guide](https://github.com/welaika/wordless#requirements-installation-and-configuration).
2. The production machine doesn't need any extra-dependency. Assets are compiled  on your development computer.
3. Install the plugin from the WP plugins directory or drop the zip inside your wp-content/plugins directory.
4. Enable the use of nice permalinks from the WP "Settings > Permalink" section.
5. Create a brand new Wordless theme directly within the WP backend, from the WP "Appearance > New Wordless Theme" section.
6. Specify the path of your ruby executables within the WP "Wordless > Preferences" menu voice.

[Wordless gem](https://github.com/welaika/wordless_gem) is command line tool to help manage your Wordless based WordPress sites.

== Changelog ==

= 0.5.6 =

= 0.5.5 =

= 0.5.4 =

= 0.5.3 =

* `44dfe`  default wordpress preferences

= 0.5.2 =

* `20444` bugfix in is_page_wpml() helper

= 0.5.1 =

* `c71e7` updated readmes

= 0.5 =

* `463d2` bump version 0.5
* `3802f` added deploy to wordpress.org script

= 0.4 =

* `e5a24` bump version
* `20868` Updated copyright
* `65a8c` update mobile detect library
* `fae2f` New Placeholder Image services
* `4db64` microfix require classes for Placehold Image services
* `89813` change lorem helpers path
* `a5326` Merge pull request #189 from namuit/master
* `7ab56` Check isset description on error messages
* `71d7f` fix permissions
* `07270` Microfixes on LoremPixel placeholder image
* `b4156` Placeholder image services moved from helpers to vendor folder
* `2ea15` remove unused function
* `4520d` wrap loaded text to avoid automatic p inclusion in output
* `67d96` link to Transifex
* `ce031` better error message
* `9174b` print error if wordless cannot write asset file
* `dc18c` Cleaned translation template
* `25d24` Merge pull request #184 from pioneerskies/base64_images
* `d0c52` add support for utf-8 in truncate helper
* `5f84e` compile mo files
* `6203c` Merge pull request #182 from pioneerskies/meta_viewport
* `5c035` Merge pull request #183 from pioneerskies/fix_title
* `bf5f7` Merge pull request #188 from johnthepink/fix-theme-error-message
* `8098e` update theme compatibility error message to include 'not'
* `0d37b` consider "data:" url as absolute
* `7ffc1` more relevant value in <title>
* `a95e6` add meta viewport by default
* `e9f81` Merge pull request #179 from mukkoo/master
* `14465` template for locales
* `8e906` italian localization
* `08166` replace we theme domain to wl
* `4bac1` Merge pull request #175 from davidmh/i18n
* `f4ac3` Merge pull request #178 from onnimonni/patch-1
* `31466` Update README.mdown
* `c3f64` Merge pull request #176 from mukkoo/master
* `dc1bd` refactoring fix
* `e9c5a` fix for "PHP Warning:  date(): It is not safe to rely on the system's timezone settings" for assets compile command.
* `bca4c` mocking __() for tests
* `a53d6` Missing string & unnecesary strings removed
* `b01ac` initial i18n - spanish
* `984ea` Merge pull request #174 from davidmh/master
* `805ae` Following convention
* `9910a` Merge pull request #173 from pioneerskies/default_jquery
* `2d950` resized images target path was missing a slash
* `1ef0a` use wp default jquery. See long comment
* `eccf9` Merge pull request #171 from davidmh/master
* `5305b` add $current_locals global to wl_yield
* `d5fcb` Merge pull request #169 from pioneerskies/update_vendor_mobiledetect
* `7f343` updated mobile_detect library
* `beb29` Merge pull request #168 from pioneerskies/yield_rename
* `87bbb` left behind a conflict
* `a3b0d` fix #155
* `f7150` Merge pull request #167 from pioneerskies/bugfix_is_page_wpml
* `73816` fix bug in is_page_wpml logic when array is passed
* `b421f` Merge pull request #165 from pioneerskies/locals
* `10f8e` Merge pull request #157 from pioneerskies/fix#156
* `ad035` Merge pull request #164 from pioneerskies/render_helpers_doc
* `9204d` Merge pull request #163 from pioneerskies/is_page_wpml
* `d8df2` locals will be available within all the rendered view
* `c74f5` code documentation
* `23b3e` added conditional helper is_page_wpml
* `4f627` Merge pull request #160 from Arkham/update-readme
* `c6439` updated documentation
* `0e7a2` trying to fix issue #156
* `8c238` Merge pull request #153 from pioneerskies/readme_bumpRubyVersion
* `782bc` updated README for using last stable ruby
* `33efa` Merge pull request #151 from mukkoo/master
* `79873` new wordless logo in vanilla theme
* `10785` Update version number to 0.4
* `aa333` Merge pull request #146 from pioneerskies/fix145
* `2a5cf` by default a new theme routes the front page
* `60b0b` Protect against falsy values in array-sh preferences
* `9507a` Avoid warning "Invalid argument supplied for foreach()"
* `9d63b` updated README with WlE logo
* `df857` updated wl logo in readme
* `89dd8` Merge pull request #144 from dz0ny/patch-1
* `21dce` Fix for Only variables should be passed by reference [PHP requirement]
* `e5f15` Merge pull request #142 from pioneerskies/pis_dev
* `83e47` Merge pull request #143 from pioneerskies/pis_doc
* `8c668` README updated...big revision
* `950d4` history updated
* `fd797` updated README
* `e56f9` updated README
* `ad157` updated HISTORY
* `75729` bumped jquery version to 1.10.1
* `a8ba8` removed no more useful rake task
* `17351` placeholder text in code documentation where it is missing
* `d8b01` Merge pull request #139 from wstucco/upstream
* `168fd` typo borken the trevisci build...ouch
* `50224` updated code documentation
* `5597a` vendor mobile_detect now in documentation
* `4add2` added documentation group @vendorclass
* `0ef96` last commit for the Doxyfile...I swear
* `b40de` Doxyfile error fixed
* `1aaa7` updated Doxyfile to latest version
* `5a6b0` doxygen conf reviewed
* `c917b` updated gitignore
* `add50` Merge pull request #138 from mukkoo/master
* `cb604` indentation fix and jQuery inclusion
* `fbf2b` added theme_path and plugin_path methods
* `68db2` priority fix
* `0e83e` Make the New Theme the first submenu item and the item to appear when clicking the parent
* `23704` add Wordless perferences submenu
* `226cb` add Wordless menu
* `1b2d5` menu images
* `75e4c` enqueue javascript with WordPress hooks
* `c3877` enqueue stylesheet with WordPress hooks
* `4fa19` fix for stylesheet and javascript extensions
* `67944` Merge pull request #136 from wstucco/upstream
* `7c78c` fixed check for absolute urls in assets_tag_helper
* `e02bb` added is_root_relative_url added test for is_realtive_url and is_root_relaitve_url fixed is_relative_url
* `6da56` added .phptidy to gitignore

== Upgrade Notice ==

= 0.5 =

* WARNING! Version 0.5 is not backward compatible! Wordless had always used `yield()` function in its template; starting from some point yield() become a reseved PHP function, so we had to rename it in Wordless code. If you get errors search and replace `yield()` inside the pugin directory with `wl_yield()`. Sorry for the inconvenient.

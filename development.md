# Deployment

* Merge your feature branch - with passing tests - in `master` with
  `git checkout master && git merge --no-ff feature` or by pull request
* On `master` update the plugin version (SEMVER) in `./wordless.php` ("Version")
  and `readme.txt` ("Stable tag") files and commit the updated files.
* do `git tag x.y.x` where *x.y.z* equals to the previously written version.
* `git push origin master --tags` to push both commits and tags

Travis should do the rest ;)

# Documentation

If you implement a feature, updating configurations, it's probably worth to document it or to
update existing documentation. Please take the right time to preserve the "sharability" of this
project to customers and to the community.

The documentation is written in restructured text format and lies in `rtfm/` folder.

## First time documenting?

Install [Sphinx](http://www.sphinx-doc.org/en/master/) with pip3 (to force Python3 to be used): `pip3 install -U sphinx sphinx-autobuild doc8 sphinx-rtd-theme`

`cd rtfm/ && sphinx-autobuild source/ build/html`

You'll have the build served in your browser at http://127.0.0.1:8000 with autoreload.

I advice to use [this estension](https://marketplace.visualstudio.com/items?itemName=lextudio.restructuredtext)
in VSCode to simplify writing and live preview. It works quite well, has a linter and intellisense.

Once the documentation will be pushed on master, it will be automatically compiled and published
at https://wordless.readthedocs.io/en/latest/.

# Changelog

A changelog for each tag/relase is mandatory to be compiled at
https://github.com/welaika/wordless/releases.

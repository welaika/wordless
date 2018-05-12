# Deployment

* Merge your feature branch - with passing tests - in `master`.
* On `master` update the plugin version (SEMVER) in `./wordless.php` ("Version") and `readme.txt` ("Stable tag") files.
* do `git tag x.y.x` where *x.y.z* equals to the previously written version.
* `git commit -m "bump version"`
* `git push origin master --tags` to push both commits and tags

Travis should do the rest ;)

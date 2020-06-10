# SimpleTest - Change Log

## [Unreleased]

**Currently working on: ...**

## [1.2.0] - 2019-09-17

* [PR#46] added import / export cookies functions
   - Browser: getCookies() & setCookies($cookies)
   - UserAgent: getCookies() & setCookies($cookies)
   - Cookies: getCookies()
* made dumper->clipString() multibyte safe (fixed #44)
* added configuration file for Phan (Static Analyzer)
* HtmlReporter: renamed $character_set to $charset and set "utf-8" as default charset
* cleanup of reference handling
  - removed explicit assign-new-object-by-reference ampersands
  - removed explicit assign-object-by-reference ampersands
  - removed unnecessary return-object-by-reference ampersands
* fixed several acceptance tests and enabled PHP server to serve the examples
* fix [SF#192](http://sourceforge.net/p/simpletest/bugs/192/) PUT request fails to encode body parameters
* added Changelog
* added .php_cs fixer configuration to keep coding style consistent
* fixed coding style and phpdoc issues through codebase
* BC break: CodeCoverage Extension uses PHP extension "sqlite3" now
  - dropped PEAR DB dependency
* fixed #17 - Access to original constructor in mocks (@mal)
* fixed return "exit code" of true/1, when tests are passing
* fixed #14 - Add support for mocking variadic methods (@36degrees)
* added support for inputs of type date and time

## [1.1.7] - 2015-09-21

* issue #12 - fix double constructor
* issue #11 - fix reference expectation
* removed PHP4 reflection support
* removed PHP4 compatibility layer

[Unreleased]: https://github.com/simpletest/simpletest/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/simpletest/simpletest/compare/v1.1.7...v1.2.0
[1.1.7]: https://github.com/simpletest/simpletest/compare/v1.1.6...v1.1.7

[PR#46]: https://github.com/simpletest/simpletest/pull/46

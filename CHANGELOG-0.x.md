# Changes in Paraunit 0.x

All notable changes of the Paraunit 0.x release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [0.7] - TBA

## [0.6.1] - 2016-06-10

### Changed

* Symfony compatibility has been extended to `<4.0`, since Symfony 3.1 has been released; we will rely on [their BC promise](http://symfony.com/doc/current/contributing/code/bc.html)
* A new approach for dockerfiles, now they will be based on the standard Docker PHP library. This will allow for a
faster image build, cleaner dockerfiles and a more precise PHP version targeting, including:
  * `docker/dockerfile-php-5.6` with the related `docker/setup-php-5.6.sh` script that will allow to build a container
  starting from the PHP 5.6 image
  * `docker/dockerfile-php-7` with the related  `docker/setup-php-7.sh` script that will allow to build a container
  starting from the PHP 7.0 image
* Paraunit now adopts [PSR-2](http://www.php-fig.org/psr/psr-2/) as a coding style
  * A `contrib/contributing.sh` has been added to tidy the code style, using `phpcbf`
  * A git pre-commit hook is enabled during the `composer install` command to warn about code style violations
* CONTRIBUTING.md file has been added, with instructions for coding style and usage of Docker images for development

## [0.6] - 2016-03-20

### Changed

* MASSIVE refactoring of the result output parsing (#31, #33) and the result printing (#37): now Paraunit fetches the 
  tests' results using PHPUnit's `--log-json` option (thanks to @taueres for the idea).
  This grants a lot of new features:
  * Parsing of tests results is more robust, it should never fail!
  * Improved performances! The JSON parsing is faster, even with unit tests
  * Fatal errors or segfaults are now grouped as "Abnormal termination"
  * When a test has an abnormal termination, the culpable test function is indicated
  * Tests with abnormal termination are printed out in full output
  * Tests executed in a test class that has a later abnormal termination are showed and counted in results anyhow
* Added support for warnings, introduced in PHPUnit 5.1 (#30)
* Added support for risky outcomes

### Fixed

* Removed `Container.php` file and `CompilerPass` class in favor of proper usage of Symfony's components
* `paraunit` bin now uses the container directly
* FinalPrinter class splitted in 3 classes 

## [0.5] - 2015-12-08

### Changed

* SQLite is now supported in the deadlock-recognition fase (issue #26, thanks @Algatux)
* Travis is testing on PHP 7.0, no more nightlies or allowed failures
* Symfony 3.0 components are now supported and compatible with Paraunit
* Retry parsing and management is improved

### Fixed

* Removed double generation of MD5 hash of process

## [0.4.4] - 2015-11-15

### Fixed

* Merged previous `v0.4` branch, there were missing fixes in previous release

## [0.4.3] - 2015-11-04

### Changed

*  `--configuration` behaviour is now identical to the same option in PHPUnit: it can accept also a path without a filename,
   and has a `-c` shortcut (default filename is `phpunit.xml.dist`) [#19]
* Added `-c` shortcut to README

## [0.4.2] - 2015-10-19

### Changed

* Unlocked supporto for PHPUnit >= 5.x
* README changed to suggest stable version of package in `composer.json` example

### Fixed

* Fixed: test stub for fatal errors fixed for HHVM & PHP7

## [0.4.1] - 2015-09-23

### Changed

* Improved: `--configuration` parameter now has the `-c` shortcut

### Fixed

* Fixed: test with fatal errors are not mistaken (and printed twice) for unknown results in the final results

## [0.4.0] - 2015-09-08

* Initial public release

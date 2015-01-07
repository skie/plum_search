# CakePHP PlumSearch Plugin Documentation

## Version notice

Currently plugin is beta version, so it is possible that there could be minor api changes.

## Installation
* [Installation](Install.md)
* [Quick sample](Quick-Sample.md)

## Detailed Documentation
* [Behavior and Filters](Filterable-Behavior-and-Filters.md)
* [Component and Parameters](Filter-Component-and-Parameters.md)
* [Search Helper](Search-Helper.md)

## Testing the Plugin
You can test using a local installation of phpunit or the phar version of it:

	cd plugins/PlumSearch
	composer update // or: php composer.phar update
	phpunit // or: php phpunit.phar

To test a specific file:

	phpunit /path/to/class.php


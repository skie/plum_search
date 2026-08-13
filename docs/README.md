# CakePHP PlumSearch Plugin Documentation

## Version notice

Currently plugin is beta version, so it is possible that there could be minor api changes.

## Documentation

For complete documentation, see [index.md](index.md). This comprehensive guide covers all aspects of using PlumSearch in your CakePHP applications.

## Quick Links

* [Complete Documentation](index.md) - Full plugin documentation
* [Installation Guide](Install.md) - Installation instructions
* [Quick Start Example](Quick-Sample.md) - Quick implementation example

## Legacy Documentation

The following documentation files are kept for reference but are superseded by the main [index.md](index.md) file:

* [Behavior and Filters](Filterable-Behavior-and-Filters.md)
* [Component and Parameters](Filter-Component-and-Parameters.md)
* [Search Helper](Search-Helper.md)
* [Range Example](Range-Example.md)

## Testing the Plugin

You can test using a local installation of phpunit or the phar version of it:

    cd plugins/PlumSearch
    composer update
    phpunit

To test a specific file:

    phpunit /path/to/class.php


DI container
============

The simplest implementation of a dependency injection container with auto-wiring.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Veejayspb/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Veejayspb/container/?branch=master)

Installation
------------

```sh
composer require veejay/container
```

Usage
-----

Basic usage.
```php
<?php

use Veejay\Container\Container;

interface SomeInterface {}
class SomeClass implements SomeInterface {}

$container = new Container;

// Class name style
$container->set(SomeInterface::class, SomeClass::class);

// Object style
$object = new SomeClass;
$container->set(SomeInterface::class, $object);

// Closure style (arrow function)
$container->set(SomeInterface::class, fn() => new SomeClass);

// Closure style (anonymous function)
$container->set(SomeInterface::class, function (Container $container) {
    return new SomeClass;
});

$service = $container->get(SomeInterface::class); // SomeClass
```

DI container
============

The simplest implementation of a dependency injection container.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Veejayspb/container/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Veejayspb/container/?branch=master)

Installation
------------

```sh
composer require veejay/container
```

Usage
-----

Basic usage example with class name as component ID.
```php
<?php

use veejay\container\Container;

$container = new Container;
// Arrow function style
$container->set(stdClass::class, fn() => new stdClass);
// Anonymous function style
$container->set(stdClass::class, function (Container $container) {
    return new stdClass;
});
$component = $container->get(stdClass::class);
```

Basic usage example with any name as component ID.
```php
<?php

use veejay\container\Container;

$container = new Container;
$container->set('component', fn() => new stdClass);
$component = $container->get('component');
```

Also you can use short syntax.
```php
<?php

use veejay\container\Container;

$container = new Container;
$container->component = fn() => new stdClass;
$component = $container->component; // stdClass
unset($container->component);
$exists = isset($container->component); // false
```

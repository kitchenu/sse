# ssel

[![Build Status](https://travis-ci.org/kitchenu/ssel.svg?branch=master)](https://travis-ci.org/kitchenu/ssel)
[![License](https://poser.pugx.org/kitchenu/ssel/license)](https://packagist.org/packages/kitchenu/ssel)

ChatWork API Client for PHP.

## Installation

```bash
$ composer require kitchenu/ssel
```
## Usage

```php
<?php

require 'vendor/autoload.php';

$app = new Ssel\App();

$app->addTimerEvent('test', function () {
    return date('Y-m-d h:i:s');
});

$app->run();
```
MalocherEventStoreModule
========================

Zend Framework 2 Module that integrates the [Malocher EventStore](https://github.com/malocher/event-store) in your ZF2 application.

[![Build Status](https://travis-ci.org/malocher/zf2-event-store-module.png?branch=master)](https://travis-ci.org/malocher/zf2-event-store-module)

## Installation

Installation of MalocherEventStoreModule uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/). Add following requirement to your composer.json


```sh
"malocher/zf2-event-store-module" : "dev-master"
```

Then add `MalocherEventStoreModule` to your `config/application.config.php``

Installation without composer is not officially supported, and requires you to install and autoload
the dependencies specified in the `composer.json`.

## Setup


Setup the EventStore using your module or application configuration. Put all EventStore options under the key malocher.eventstore. 
```php
  'malocher.eventstore' => array(
        ...
  )
```
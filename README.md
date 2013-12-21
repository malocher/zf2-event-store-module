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

Setup the EventStore using your module or application configuration. Put all EventStore options under the key `malocher.eventstore`. 
```php
  'malocher.eventstore' => array(
        ...
  )
```
The MalocherEventStoreModule ships with an [Zf2EventStoreAdapter](https://github.com/malocher/zf2-event-store-module/blob/master/src/MalocherEventStoreModule/Adapter/Zf2EventStoreAdapter.php).
You can setup the ZF2 DB Adapter via the `malocher.eventstore.adapter.connection` configuration. Use the 
same adapter options like you would do with a normal ZF2 DB Adapter. Checkout [ZF2 docs](http://framework.zend.com/manual/2.1/en/modules/zend.db.adapter.html#creating-an-adapter-quickstart)
for all available options. Here is an example that setup a SQLite in memory adapter:

```php
'malocher.eventstore' => array(
    'adapter' => array(
        'MalocherEventStoreModule\Adapter\Zf2EventStoreAdapter' => array(
            'connection' => array(
                'driver' => 'Pdo_Sqlite',
                'database' => ':memory:'
            )
        )
    ),
)
```

Right now you have to create the database schema yourself. We are working on a command line tool
to help you setup your EventStore. The store needs one `snapshot` table and an
`event stream` table per EventSourcedObject (AggregateRoot, if your using DDD).

Here is the example schema used for SQLite:
```sql
-- create one snapshot table
CREATE TABLE snapshot 
(
    id INTEGER PRIMARY KEY,
    sourceType TEXT,
    sourceId  TEXT,
    snapshotVersion INTEGER
)

-- create a table for each of your EventSourcedObjects
-- by default My\Namespaced\Object leads to the table name object_stream
CREATE TABLE <object name here>_stream 
(
    eventId TEXT PRIMARY KEY,
    sourceId TEXT,
    sourceVersion INTEGER,
    eventClass TEXT,
    payload TEXT,
    eventVersion REAL,
    timestamp INTEGER
)
```

## Usage

Use the ZF2 ServiceManger to get an instance of the Malocher\EventStore. A ServiceFactory
passes your malocher.eventstore configuration to the EventStore, so it is ready to
save and load your EventSourcedObjects.
```php
use Malocher\EventStore\EventStore;
use Malocher\EventStore\StoreEvent\PostPersistEvent;

/* @var $eventStore EventStore */
$eventStore = $serviceManager->get('malocher.eventstore');

//You can listen to the eventstore.events_post_persist event to be notified 
//when an object was changed
$eventstore->events()->addListener(PostPersistEvent::NAME, function(PostPersistEvent $e) {
    foreach ($e->getPersistedEvents() as $persitedEvent) {
        if ($persistedEvent instanceof \My\Event\UserNameChangedEvent) {
            $oldName = $persistedEvent->getPayload()['oldName'];
            $newName = $persistedEvent->getPayload()['newName'];
            echo "Name changed from $oldName to $newName";
        }
    }
});

$userRepository = $eventStore->getRepository('My\User');

//Assuming username is the primary key 
$user = $userRepository->find('John');

$user->changeName('Malocher');

$userRepository->save($user);

//Output: Name changed from John to Malocher
```

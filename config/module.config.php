<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'malocher.eventstore' => 'MalocherEventStoreModule\Service\EventStoreFactory',
            'malocher.eventstore.eventdispatcher' => 'MalocherEventStoreModule\EventDispatcher\Service\EventManagerProxyFactory',
        )
    )
);
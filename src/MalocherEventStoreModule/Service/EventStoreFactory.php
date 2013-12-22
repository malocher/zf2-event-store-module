<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\Service;

use Malocher\EventStore\EventStore;
use Malocher\EventStore\Configuration\Configuration;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
/**
 * EventStoreFactory
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStoreFactory implements FactoryInterface
{
    /**
     * Create new EventStore instance
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * 
     * @return EventStore
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $globalConfig = array();
        $localConfig = array();
        
        $appConfig = $serviceLocator->get('configuration');
        
        if (isset($appConfig['malocher.eventstore'])) {
            $globalConfig = $appConfig['malocher.eventstore'];
        }
        
        if (file_exists('config/eventstore.config.php')) {
            $localConfig = include 'config/eventstore.config.php';
        }
        
        $esConfig = new Configuration(array_merge($globalConfig, $localConfig));
        
        $esConfig->setEventDispatcher($serviceLocator->get('malocher.eventstore.eventdispatcher'));
        
        return new EventStore($esConfig);
    }

}

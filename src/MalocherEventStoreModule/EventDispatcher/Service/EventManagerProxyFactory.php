<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\EventDispatcher\Service;

use MalocherEventStoreModule\EventDispatcher\EventManagerProxy;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
/**
 *  EventManagerProxyFactory
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventManagerProxyFactory implements FactoryInterface
{
    /**
     * Create an instance of EventManagerProxy
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * 
     * @return EventManagerProxy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $proxy = new EventManagerProxy();
        $em = new EventManager();
        $em->setSharedManager($serviceLocator->get('SharedEventManager'));
        $proxy->setEventManager($em);
        
        return $proxy;
    }
}

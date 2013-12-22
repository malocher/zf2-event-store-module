<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\EventDispatcher;

use MalocherEventStoreModule\EventDispatcher\EventDecorator\DecoratorFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface as SymfonyEventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Zend\EventManager\EventManager;
/**
 * EventManagerProxy
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventManagerProxy implements SymfonyEventDispatcherInterface
{
    /**
     * @var EventManager 
     */
    protected $eventManager;
    
    /**
     * Proxy all unknown methods to ZF2 EventManager
     * 
     * @param string $name Method name
     * @param array  $arguments Org. method arguments
     * 
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->eventManager, $name), $arguments);
    }
    
    /**
     * Set EventManager instance
     * 
     * @param EventManager $eventManager
     * 
     * @return void
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->eventManager->setIdentifiers(array('EventStore'));
    }
    
    /**
     * Get EventManager instance
     *      
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->eventManager->attach($eventName, $listener, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function addSubscriber(SymfonyEventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, array($subscriber, $params));
            } elseif (is_string($params[0])) {
                $this->addListener($eventName, array($subscriber, $params[0]), isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventName, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($eventName, SymfonyEvent $event = null)
    {
        $decoratedEvent = DecoratorFactory::decorate($event);
        $decoratedEvent->setName($eventName);
        
        return $this->eventManager->trigger($decoratedEvent);
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners($eventName = null)
    {
        if (is_null($eventName)) {
            $listeners = array();
            
            foreach ($this->eventManager->getEvents() as $eventName) {
                $listeners[$eventName] = $this->getListeners($eventName);
            }
            
            return $listeners;
        }
        
        return $this->eventManager->getListeners($eventName)->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners($eventName = null)
    {
        return (bool)count($this->getListeners($eventName));
    }

    /**
     * {@inheritDoc}
     */
    public function removeListener($eventName, $listener)
    {
        $listeners = $this->eventManager->getListeners($eventName);
        
        foreach ($listeners as $callbackHanler) {
            $callback = $callbackHanler->getCallback();
            
            if (is_array($callback)) {                
                if (is_array($listener)) {                      
                    if ($callback[0] === $listener[0] && $callback[1] === $listener[1]) {
                        return $this->eventManager->detach($callbackHanler);
                    }
                } else {
                    continue;
                }
            }
            
            if ($callback === $listener) {
                return $this->eventManager->detach($callbackHanler);
            }
        }
        
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function removeSubscriber(SymfonyEventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->removeListener($eventName, array($subscriber, $listener[0]));
                }
            } else {
                $this->removeListener($eventName, array($subscriber, is_string($params) ? $params : $params[0]));
            }
        }
    }
}

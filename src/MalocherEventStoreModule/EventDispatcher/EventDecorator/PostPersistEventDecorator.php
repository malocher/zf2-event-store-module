<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\EventDispatcher\EventDecorator;

use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Zend\EventManager\EventInterface as ZendEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcher;
/**
 *  PostPersistEventDecorator
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PostPersistEventDecorator extends PostPersistEvent implements ZendEventInterface
{
    /**
     *
     * @var PostPersistEvent 
     */
    protected $postPersistEvent;
    
    public function __construct(PostPersistEvent $postPersistEvent)
    {
        $this->postPersistEvent = $postPersistEvent;
    }
    
    /**
     * Get FQDN of EventSourcedObject
     * 
     * @return string
     */
    public function getSourceFQDN()
    {
        return $this->postPersistEvent->getSourceFQDN();
    }
    
    /**
     * Get id of the EventSourcedObject
     * 
     * @return string
     */
    public function getSourceId()
    {
        return $this->postPersistEvent->getSourceId();
    }
    
    /**
     * Get the EventSourcedObject
     * 
     * @return EventSourcedInterface
     */
    public function getSource()
    {
        return $this->postPersistEvent->getSource();
    }
    
    /**
     * Get the persisted events
     * 
     * @return EventInterface[]
     */
    public function getPersistedEvents()
    {
        return $this->postPersistEvent->getPersistedEvents();
    }

    /**
     * {@inheritDoc}
     */
    public function getParam($name, $default = null)
    {
        switch($name) {
            case 'sourceFQDN':
                return $this->postPersistEvent->getSourceFQDN();
            case 'sourceId':
                return $this->postPersistEvent->getSourceId();
            case 'source':
                return $this->postPersistEvent->getSource();
            case 'persistedEvents':
                return $this->postPersistEvent->getPersistedEvents();
            default:
                return $default;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParams()
    {
        return array(
            'sourceFQDN' => $this->postPersistEvent->getSourceFQDN(),
            'sourceId' => $this->postPersistEvent->getSourceId(),
            'source' => $this->postPersistEvent->getSource(),
            'persistedEvents' => $this->postPersistEvent->getPersistedEvents()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->postPersistEvent->getSource();
    }

    /**
     * {@inheritDoc}
     */
    public function propagationIsStopped()
    {
        return $this->postPersistEvent->isPropagationStopped();
    }
    
    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped()
    {
        return $this->postPersistEvent->isPropagationStopped();
    }
    
    /**
     * {@inheritDoc}
     */
    public function stopPropagation($flag = true)
    {
        $this->postPersistEvent->stopPropagation($flag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setDispatcher(SymfonyEventDispatcher $dispatcher)
    {
        $this->postPersistEvent->setDispatcher($dispatcher);
    }

    /**
     * {@inheritDoc}
     */
    public function getDispatcher()
    {
        return $this->postPersistEvent->getDispatcher();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->postPersistEvent->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->postPersistEvent->setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setParam($name, $value)
    {
        throw new \BadMethodCallException('Set param is not supported by the PostPersistEvent');
    }

    /**
     * {@inheritDoc}
     */
    public function setParams($params)
    {
        throw new \BadMethodCallException('Set params is not supported by the PostPersistEvent');
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget($target)
    {
        throw new \BadMethodCallException('Set target is not supported by the PostPersistEvent');
    }
}

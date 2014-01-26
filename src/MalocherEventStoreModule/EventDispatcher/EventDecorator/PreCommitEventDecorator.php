<?php

/*
 * This file is part of the codeliner/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\EventDispatcher\EventDecorator;

use Malocher\EventStore\StoreEvent\PreCommitEvent;
use Malocher\EventStore\EventStore;
use Zend\EventManager\EventInterface as ZendEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcher;
/**
 * Class PreCommitEventDecorator
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PreCommitEventDecorator extends PreCommitEvent implements ZendEventInterface
{
    /**
     *
     * @var PreCommitEvent 
     */
    protected $preCommitEvent;
    
    public function __construct(PreCommitEvent $preCommitEvent)
    {
        $this->preCommitEvent = $preCommitEvent;
    }
    
    /**
     * 
     * @return EventStore
     */
    public function getEventStore()
    {
        return $this->preCommitEvent->getEventStore();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getParam($name, $default = null)
    {
        switch($name) {
            case 'eventStore':
                return $this->getEventStore();
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
            'eventStore' => $this->getEventStore()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->getEventStore();
    }

    /**
     * {@inheritDoc}
     */
    public function propagationIsStopped()
    {
        return $this->preCommitEvent->isPropagationStopped();
    }
    
    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped()
    {
        return $this->preCommitEvent->isPropagationStopped();
    }
    
    /**
     * {@inheritDoc}
     */
    public function stopPropagation($flag = true)
    {
        $this->preCommitEvent->stopPropagation($flag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setDispatcher(SymfonyEventDispatcher $dispatcher)
    {
        $this->preCommitEvent->setDispatcher($dispatcher);
    }

    /**
     * {@inheritDoc}
     */
    public function getDispatcher()
    {
        return $this->preCommitEvent->getDispatcher();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->preCommitEvent->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->preCommitEvent->setName($name);
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

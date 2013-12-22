<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest\EventDispatcher;

use MalocherEventStoreModuleTest\TestCase;
use MalocherEventStoreModuleTest\Bootstrap;
use MalocherEventStoreModule\EventDispatcher\EventManagerProxy;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use MalocherEventStoreModuleTest\Mock\User;
use MalocherEventStoreModuleTest\Mock\PostPersistEventSubscriberMock;
/**
 *  EventManagerProxyTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventManagerProxyTest extends TestCase
{
    /**
     * @var EventManagerProxy
     */
    protected $eventManagerProxy;
    
    protected function setUp()
    {
        $this->eventManagerProxy = Bootstrap::getServiceManager()
            ->get('malocher.eventstore.eventdispatcher');
    }
    
    protected function tearDown()
    {
        $this->eventManagerProxy->clearListeners(PostPersistEvent::NAME);
        Bootstrap::getServiceManager()->get('SharedEventManager')->clearListeners('EventStore');
    }


    public function testAddListenerAndDispatch()
    {
        $callbackCalled = false;
        
        $this->eventManagerProxy->addListener(
            PostPersistEvent::NAME, 
            function(PostPersistEvent $e) use (&$callbackCalled) {
                $callbackCalled = true;
            }
        );
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
        
        $this->assertTrue($callbackCalled);
    }
    
    public function testAttachAndDispatch()
    {
        $callbackCalled = false;
        
        $this->eventManagerProxy->attach(
            PostPersistEvent::NAME, 
            function(PostPersistEvent $e) use (&$callbackCalled) {
                $callbackCalled = true;
            }
        );
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
        
        $this->assertTrue($callbackCalled);
    }
    
    public function testAttachViaSharedEventManagerAndDispatch()
    {
        $callbackCalled = false;
        
        $sharedEventManager = Bootstrap::getServiceManager()->get('SharedEventManager');
        
        $sharedEventManager->attach(
            'EventStore', 
            PostPersistEvent::NAME, 
            function(PostPersistEvent $e) use (&$callbackCalled) {
                $callbackCalled = true;
            }
        );
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
        
        $this->assertTrue($callbackCalled);
    }
    
    public function testAddSubscriberAndDispatch()
    {
        $subscriber = new PostPersistEventSubscriberMock();
        
        $this->eventManagerProxy->addSubscriber($subscriber);
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
                
        $this->assertTrue($subscriber->wasCalled());
    }
    
    public function testHasListeners()
    {
        $callback = function(PostPersistEvent $e) {
            
        };
        
        $this->eventManagerProxy->addListener(PostPersistEvent::NAME, $callback);
        
        $this->assertTrue($this->eventManagerProxy->hasListeners());
        $this->assertTrue($this->eventManagerProxy->hasListeners(PostPersistEvent::NAME));        
    }
    
    public function testGetListeners()
    {
        $callback = function(PostPersistEvent $e) {
            
        };
        
        $this->eventManagerProxy->addListener(PostPersistEvent::NAME, $callback);
        
        $listeners = $this->eventManagerProxy->getListeners(PostPersistEvent::NAME);
        
        $this->assertEquals(1, count($listeners));
        
        $this->assertSame($callback, $listeners[0]->getCallback());
        
        $listeners = $this->eventManagerProxy->getListeners();
        
        $this->assertTrue(isset($listeners[PostPersistEvent::NAME]));
        
        $this->assertSame($callback, $listeners[PostPersistEvent::NAME][0]->getCallback());
    }
    
    public function testRemoveListener()
    {
        $called = false;
        
        $callback = function(PostPersistEvent $e) use (&$called) {
            $called = true;
        };
        
        $this->eventManagerProxy->addListener(PostPersistEvent::NAME, $callback);
        
        $this->eventManagerProxy->removeListener(PostPersistEvent::NAME, $callback);
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
        
        $this->assertFalse($called);
    }
    
    public function testRemoveSubscriber()
    {
        $subscriber = new PostPersistEventSubscriberMock();
        
        $this->eventManagerProxy->addSubscriber($subscriber);
        
        $this->eventManagerProxy->removeSubscriber($subscriber);
        
        $this->eventManagerProxy->dispatch(PostPersistEvent::NAME, $this->getPostPersistEvent());
                
        $this->assertFalse($subscriber->wasCalled());
    }
    
    protected function getPostPersistEvent()
    {
        $user = new User('1');
        
        $user->changeName('Malocher');
        
        return new PostPersistEvent($user, $user->getPendingEvents());
    }
}

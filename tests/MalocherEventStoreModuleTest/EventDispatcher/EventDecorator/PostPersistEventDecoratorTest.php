<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest\EventDispatcher\EventDecorator;

use MalocherEventStoreModuleTest\TestCase;
use MalocherEventStoreModule\EventDispatcher\EventDecorator\PostPersistEventDecorator;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use MalocherEventStoreModuleTest\Mock\User;
/**
 * PostPersistEventDecoratorTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PostPersistEventDecoratorTest extends TestCase
{
    /**
     *
     * @var PostPersistEventDecorator 
     */
    protected $postPersistEventDecorator;
    
    /**
     *
     * @var PostPersistEvent 
     */
    protected $postPersistEvent;


    protected function setUp()
    {
        $user = new User('1');
        
        $user->changeName('Malocher');
        
        $this->postPersistEvent = new PostPersistEvent($user, $user->getPendingEvents());
        
        $this->postPersistEventDecorator = new PostPersistEventDecorator($this->postPersistEvent);
    }
    
    public function testGetSourceFQDN()
    {
        $this->assertEquals(
            $this->postPersistEvent->getSourceFQDN(), 
            $this->postPersistEventDecorator->getSourceFQDN()
        );
    }
    
    public function testGetSourceId()
    {
        $this->assertEquals(
            $this->postPersistEvent->getSourceId(), 
            $this->postPersistEventDecorator->getSourceId()
        );
    }
    
    public function testGetSource()
    {
        $this->assertEquals(
            $this->postPersistEvent->getSource(), 
            $this->postPersistEventDecorator->getSource()
        );
    }
    
    public function testGetPersistedEvents()
    {
        $this->assertEquals(
            $this->postPersistEvent->getPersistedEvents(), 
            $this->postPersistEventDecorator->getPersistedEvents()
        );
    }
    
    public function testGetParam()
    {
        $this->assertEquals(
            $this->postPersistEvent->getSourceFQDN(), 
            $this->postPersistEventDecorator->getParam('sourceFQDN')
        );
        
        $this->assertEquals(
            $this->postPersistEvent->getSourceId(), 
            $this->postPersistEventDecorator->getParam('sourceId')
        );
        
        $this->assertEquals(
            $this->postPersistEvent->getSource(), 
            $this->postPersistEventDecorator->getParam('source')
        );
        
        $this->assertEquals(
            $this->postPersistEvent->getPersistedEvents(), 
            $this->postPersistEventDecorator->getParam('persistedEvents')
        );
        
        $this->assertEquals(123, $this->postPersistEventDecorator->getParam('unknown', 123));
    }
    
    public function testGetParams()
    {
        $check = array(
            'sourceFQDN' => $this->postPersistEvent->getSourceFQDN(),
            'sourceId' => $this->postPersistEvent->getSourceId(),
            'source' => $this->postPersistEvent->getSource(),
            'persistedEvents' => $this->postPersistEvent->getPersistedEvents()
        );
        
        $this->assertEquals($check, $this->postPersistEventDecorator->getParams());
    }
    
    public function testGetTarget()
    {
        $this->assertEquals(
            $this->postPersistEvent->getSource(), 
            $this->postPersistEventDecorator->getTarget()
        );
    }
    
    public function testStopPropagation()
    {
        $this->postPersistEventDecorator->stopPropagation();
        
        $this->assertTrue($this->postPersistEventDecorator->isPropagationStopped());
        $this->assertTrue($this->postPersistEventDecorator->propagationIsStopped());
    }
    
    public function testSetAndGetName()
    {
        $this->postPersistEventDecorator->setName(PostPersistEvent::NAME);
        
        $this->assertEquals(PostPersistEvent::NAME, $this->postPersistEventDecorator->getName());
    }
}

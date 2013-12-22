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
use MalocherEventStoreModule\EventDispatcher\EventDecorator\DecoratorFactory;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use MalocherEventStoreModuleTest\Mock\User;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
/**
 *  DecoratorFactoryTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DecoratorFactoryTest extends TestCase
{
    public function testDecorate()
    {
        $mockedUser = new User('1');
        $postPersistEvent = new PostPersistEvent($mockedUser, array());
        
        $decoratedEvent = DecoratorFactory::decorate($postPersistEvent);
        
        $this->assertInstanceOf(
            'MalocherEventStoreModule\EventDispatcher\EventDecorator\PostPersistEventDecorator', 
            $decoratedEvent
        );
        
        $this->assertInstanceOf('Malocher\EventStore\StoreEvent\PostPersistEvent', $decoratedEvent);
        
        $this->assertInstanceOf('Zend\EventManager\EventInterface', $decoratedEvent);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testDecorateFailOnUnknownEvent()
    {
        DecoratorFactory::decorate(new SymfonyEvent());
    }
}

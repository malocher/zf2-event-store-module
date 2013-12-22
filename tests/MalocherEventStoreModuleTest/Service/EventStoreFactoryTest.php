<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest\Service;

use MalocherEventStoreModuleTest\TestCase;
use MalocherEventStoreModuleTest\Bootstrap;
/**
 *  EventStoreFactoryTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class EventStoreFactoryTest extends TestCase
{
    protected function setUp()
    {
        chdir(getcwd() . '/..');
    }
    
    protected function tearDown()
    {
        chdir(getcwd() . '/tests');
    }


    public function testCreateService()
    {        
        $eventStore = Bootstrap::getServiceManager()->get('malocher.eventstore');
        
        $this->assertInstanceOf('Malocher\EventStore\EventStore', $eventStore);
        
        $this->assertInstanceOf(
            'MalocherEventStoreModule\EventDispatcher\EventManagerProxy', 
            $eventStore->events()
        );
    }
}

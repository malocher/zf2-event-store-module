<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest\Adapter;

use MalocherEventStoreModule\Adapter\Zf2EventStoreAdapter;
use MalocherEventStoreModuleTest\TestCase;
use MalocherEventStoreModuleTest\Mock\Event\UserNameChangedEvent;
use MalocherEventStoreModuleTest\Mock\Event\UserEmailChangedEvent;
use Malocher\EventStore\EventSourcing\SnapshotEvent;
/**
 *  Zf2EventStoreAdapterTest
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Zf2EventStoreAdapterTest extends TestCase
{
    /**
     *
     * @var Zf2EventStoreAdapter 
     */
    protected $zf2EventStoreAdapter;
    
    protected function setUp()
    {
        $this->initEventStoreAdapter();
        
        $this->zf2EventStoreAdapter = $this->getEventStoreAdapter();
        
        $this->zf2EventStoreAdapter->createSchema(array('User'));
    }
    
    public function testAddToStreamAndLoadStream()
    {
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->zf2EventStoreAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $stream = $this->zf2EventStoreAdapter->loadStream('User', '1');
        
        $this->assertEquals(array($userNameChangedEvent, $userEmailChangedEvent), $stream);
    }
    
    public function testCreateSnapshotAndGetCurrentSnapshotVersion()
    {
        $this->assertEquals(0, $this->zf2EventStoreAdapter->getCurrentSnapshotVersion('User', '1'));
        
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->zf2EventStoreAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $snapshotEvent = new SnapshotEvent(array('name' => 'Malocher', 'email' => 'my.mail@getmalocher.org'), '102', $yesterdayTimestamp, 2.0);
        $snapshotEvent->setSourceId('1');
        $snapshotEvent->setSourceVersion(3);
        
        $this->zf2EventStoreAdapter->createSnapshot('User', '1', $snapshotEvent);
        
        $this->assertEquals(3, $this->zf2EventStoreAdapter->getCurrentSnapshotVersion('User', '1'));
        
        $this->assertEquals(
            array($userNameChangedEvent, $userEmailChangedEvent, $snapshotEvent), 
            $this->zf2EventStoreAdapter->loadStream('User', '1')
        );
    }
    
    public function testLoadStreamFromVersionOn()
    {
        $yesterdayTimestamp = time() - 86400;
        $userNameChangedEvent = new UserNameChangedEvent(array('name' => 'Malocher'), '100', $yesterdayTimestamp, 2.0);
        $userNameChangedEvent->setSourceId('1');
        $userNameChangedEvent->setSourceVersion(1);
        
        $userEmailChangedEvent = new UserEmailChangedEvent(array('email' => 'my.mail@getmalocher.org'), '101', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent->setSourceId('1');
        $userEmailChangedEvent->setSourceVersion(2);
        
        $this->zf2EventStoreAdapter->addToStream('User', '1', array($userNameChangedEvent, $userEmailChangedEvent));
        
        $snapshotEvent = new SnapshotEvent(array('name' => 'Malocher', 'email' => 'my.mail@getmalocher.org'), '102', $yesterdayTimestamp, 2.0);
        $snapshotEvent->setSourceId('1');
        $snapshotEvent->setSourceVersion(3);
        
        $this->zf2EventStoreAdapter->createSnapshot('User', '1', $snapshotEvent);
        
        $userEmailChangedEvent2 = new UserEmailChangedEvent(array('email' => 'contact@getmalocher.org'), '103', $yesterdayTimestamp, 2.0);
        $userEmailChangedEvent2->setSourceId('1');
        $userEmailChangedEvent2->setSourceVersion(4);
        
        $this->zf2EventStoreAdapter->addToStream('User', '1', array($userEmailChangedEvent2));
        
        $this->assertEquals(
            array($snapshotEvent, $userEmailChangedEvent2), 
            $this->zf2EventStoreAdapter->loadStream(
                'User', 
                '1', 
                $this->zf2EventStoreAdapter->getCurrentSnapshotVersion('User', '1')
            )
        );
    }
}

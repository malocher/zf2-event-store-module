<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest\Mock;

use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 *  PostPersistEventSubscriberMock
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class PostPersistEventSubscriberMock implements EventSubscriberInterface 
{
    protected $called = false;
    
    public static function getSubscribedEvents()
    {
        return array(
            PostPersistEvent::NAME => array('onPostPersist', 0),
        );
    }

    public function onPostPersist(PostPersistEvent $e)
    {
        $this->called = true;
    }
    
    public function wasCalled()
    {
        return $this->called;
    }
}

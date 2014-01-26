<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Manfred Weber <crafics@php.net> and Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\EventDispatcher\EventDecorator;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Malocher\EventStore\StoreEvent\PostPersistEvent;
use Malocher\EventStore\StoreEvent\PreCommitEvent;
/**
 * DecoratorFactory
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class DecoratorFactory
{
    public static function decorate(SymfonyEvent $e)
    {
        if ($e instanceof PostPersistEvent) {
            return new PostPersistEventDecorator($e);
        }
        
        if ($e instanceof PreCommitEvent) {
            return new PreCommitEventDecorator($e);
        }
        
        throw new \InvalidArgumentException(
            sprintf(
                'The event type -%s- is not supported',
                get_class($e)
            )
        );
    }
}

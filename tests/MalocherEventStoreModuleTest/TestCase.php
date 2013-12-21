<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModuleTest;

use MalocherEventStoreModule\Adapter\Zf2EventStoreAdapter;
/**
 * MalocherEventStoreModule TestCase
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var DoctrineDbalAdapter 
     */
    protected $zf2EventStoreAdapter;
    
    protected function initEventStoreAdapter()
    {
        $options = array(
            'connection' => array(
                'driver' => 'Pdo_Sqlite',
                'database' => ':memory:'
            )
        );
        
        $this->zf2EventStoreAdapter = new Zf2EventStoreAdapter($options);
    }
    
    /**
     * @return AdapterInterface
     */
    protected function getEventStoreAdapter()
    {
        return $this->zf2EventStoreAdapter;
    }
}

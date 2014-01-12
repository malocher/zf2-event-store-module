<?php
/*
 * This file is part of the malocher/zf2-event-store-module package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MalocherEventStoreModule\Adapter;

use Malocher\EventStore\Adapter\AdapterInterface;
use Malocher\EventStore\Adapter\AdapterException;
use Malocher\EventStore\EventSourcing\EventInterface;
use Malocher\EventStore\EventSourcing\SnapshotEvent;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Platform;
/**
 * EventStore Adapter Zf2TableGatewayAdapter
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Zf2EventStoreAdapter implements AdapterInterface
{
    /**
     * @var ZendDbAdapter 
     */
    protected $dbAdatper;
    
    /**
     *
     * @var TableGateway[] 
     */
    protected $tableGateways;


    /**
     *
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * Custom sourceType to table mapping
     * 
     * @var array 
     */
    protected $sourceTypeTableMap = array();
    
    /**
     * Name of the table that contains snapshot metadata
     * 
     * @var string 
     */
    protected $snapshotTable = 'snapshot';
    
    /**
     * {@inheritDoc}
     */
    public function __construct(array $configuration)
    {
        if (!isset($configuration['connection'])) {
            throw AdapterException::configurationException('Adapter connection configuration is missing');
        }
        
        if (isset($options['serializer'])) {
            $this->serializer = $options['serializer'];
        }
        
        if (isset($options['source_table_map'])) {
            $this->sourceTypeTableMap = $options['source_table_map'];
        }
        
        if (isset($options['snapshot_table'])) {
            $this->snapshotTable = $options['snapshot_table'];
        }
        
        $this->dbAdatper = new ZendDbAdapter($configuration['connection']);
    }
    
    /**
     * {@inheritDoc}
     */
    public function loadStream($sourceFQCN, $sourceId, $version = null)
    {
        $tableGateway = $this->getTablegateway($sourceFQCN);
        
        $sql = $tableGateway->getSql();
        
        $where = new \Zend\Db\Sql\Where();
        
        $where->equalTo('sourceId', $sourceId);
        
        if (!is_null($version)) {
            $where->AND->greaterThanOrEqualTo('sourceVersion', $version);
        }
        
        $select = $sql->select()->where($where)->order('sourceVersion');
        
        $eventsData = $tableGateway->selectWith($select);
        
        $events = array();
        
        foreach ($eventsData as $eventData) {
            $eventClass = $eventData->eventClass;
            
            $payload = $this->getSerializer()->deserialize($eventData->payload, 'array', 'json');
            
            $event = new $eventClass($payload, $eventData->eventId, (int)$eventData->timestamp, (float)$eventData->eventVersion);
            $event->setSourceVersion((int)$eventData->sourceVersion);
            $event->setSourceId($sourceId);
            
            $events[] = $event;
        }
        
        return $events;
    }
    
    /**
     * {@inheritDoc}
     */
    public function addToStream($sourceFQCN, $sourceId, $events)
    {
        try {
            $this->dbAdatper->getDriver()->getConnection()->beginTransaction();
            foreach ($events as $event) {
                $this->insertEvent($sourceFQCN, $sourceId, $event);
            }
            $this->dbAdatper->getDriver()->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->dbAdatper->getDriver()->getConnection()->rollback();
            throw $ex;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function createSnapshot($sourceFQCN, $sourceId, SnapshotEvent $event)
    {
        try {
            $this->dbAdatper->getDriver()->getConnection()->beginTransaction();
            
            $this->insertEvent($sourceFQCN, $sourceId, $event);
            
            $snapshotMetaData = array(
                'sourceType' => $sourceFQCN,
                'sourceId' => $sourceId,
                'snapshotVersion' => $event->getSourceVersion()
            );
            
            $tableGateway = new TableGateway('snapshot', $this->dbAdatper);
            
            $tableGateway->insert($snapshotMetaData);
            
            $this->dbAdatper->getDriver()->getConnection()->commit();
        } catch (\Exception $ex) {
            $this->dbAdatper->getDriver()->getConnection()->rollback();
            throw $ex;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentSnapshotVersion($sourceFQCN, $sourceId)
    {
        $tableGateway = new TableGateway('snapshot', $this->dbAdatper);
        
        $row = $tableGateway->select(array(
            'sourceType' => $sourceFQCN,
            'sourceId'   => $sourceId
        ))->current();
        
        if ($row) {
            return (int)$row->snapshotVersion;
        }
        
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function createSchema(array $streams)
    {
        if ($this->dbAdatper->getPlatform() instanceof Platform\Sqlite) {
            $this->createSqliteSchema($streams);
            return true;
        }
        
        throw new \BadMethodCallException(
            sprintf(
                'The createSchema command is not supported for %s. Please create the schema of your own or try doctine/dbal adapter instead.',
                $this->dbAdatper->getPlatform()->getName()
            )
        );
    }
    
    /**
     * {@inheritDoc}
     */
    public function dropSchema(array $streams)
    {
        if ($this->dbAdatper->getPlatform() instanceof Platform\Sqlite) {
            $this->dropSqliteSchema($streams);
            return true;
        }
        
        throw new \BadMethodCallException(
            sprintf(
                'The dropSchema command is not supported for %s. Please create the schema of your own or try doctine/dbal adapter instead.',
                $this->dbAdatper->getPlatform()->getName()
            )
        );
    }
    
    /**
     * {@inheritDoc}
     */
    public function exportSchema($file, $snapshots_only)
    {
        throw new \BadMethodCallException(
            sprintf(
                'The exportSchema command is not supported for %s. Please create the schema of your own or try doctine/dbal adapter instead.',
                $this->dbAdatper->getPlatform()->getName()
            )
        );
    }
    
    /**
     * {@inheritDoc}
     */
    public function importSchema($file)
    {
        throw new \BadMethodCallException(
            sprintf(
                'The importSchema command is not supported for %s. Please create the schema of your own or try doctine/dbal adapter instead.',
                $this->dbAdatper->getPlatform()->getName()
            )
        );
    }
    
    /**
     * Insert an event
     * 
     * @param string         $sourceFQCN
     * @param string         $sourceId
     * @param EventInterface $e
     * 
     * @return void
     */
    protected function insertEvent($sourceFQCN, $sourceId, EventInterface $e)
    {        
        $eventData = array(
            'sourceId' => $sourceId,
            'sourceVersion' => $e->getSourceVersion(),
            'eventClass' => get_class($e),
            'payload' => $this->getSerializer()->serialize($e->getPayload(), 'json'),
            'eventId' => $e->getId(),
            'eventVersion' => $e->getVersion(),
            'timestamp' => $e->getTimestamp()
        );
        
        $tableGateway = $this->getTablegateway($sourceFQCN);
        
        $tableGateway->insert($eventData);
    }
    
    /**
     * Get the corresponding Tablegateway of the given $sourceFQCN
     * 
     * @param string $sourceFQCN
     * 
     * @return TableGateway
     */
    protected function getTablegateway($sourceFQCN)
    {
        if (!isset($this->tableGateways[$sourceFQCN])) {
            $this->tableGateways[$sourceFQCN] = new TableGateway($this->getTable($sourceFQCN), $this->dbAdatper);
        }
        
        return $this->tableGateways[$sourceFQCN];
    }
    
    /**
     * 
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (is_null($this->serializer)) {
            $this->serializer = SerializerBuilder::create()->build();
        }
        
        return $this->serializer;
    }
    
    /**
     * Get tablename for given sourceType
     * 
     * @param $sourceFQCN
     * @return string
     */
    protected function getTable($sourceFQCN)
    {
        if (isset($this->sourceTypeTableMap[$sourceFQCN])) {
            $tableName = $this->sourceTypeTableMap[$sourceFQCN];
        } else {
            $tableName = strtolower($this->getShortSourceType($sourceFQCN)) . "_stream";
        }
        
        return $tableName;
    }

    /**
     * @param $sourceFQCN
     * @return string
     */
    protected function getShortSourceType($sourceFQCN)
    {
        return join('', array_slice(explode('\\', $sourceFQCN), -1));
    }
    
    protected function createSqliteSchema(array $streams)
    {
        $snapshot_sql = 'CREATE TABLE IF NOT EXISTS snapshot '
            . '('
                . 'id INTEGER PRIMARY KEY,'
                . 'sourceType TEXT,'
                . 'sourceId  TEXT,'
                . 'snapshotVersion INTEGER'
            . ')';

        $this->dbAdatper->getDriver()->getConnection()->execute($snapshot_sql);

        foreach ($streams as $stream) {
            $streamSql = 'CREATE TABLE ' . $this->getTable($stream) . ' '
                . '('
                    . 'eventId TEXT PRIMARY KEY,'
                    . 'sourceId TEXT,'
                    . 'sourceVersion INTEGER,'
                    . 'eventClass TEXT,'
                    . 'payload TEXT,'
                    . 'eventVersion REAL,'
                    . 'timestamp INTEGER'
                . ')';
            
            $this->dbAdatper->getDriver()->getConnection()->execute($streamSql);
        }
    }
    
    protected function dropSqliteSchema(array $streams)
    {
        foreach ($streams as $stream) {
            $streamSql = 'DROP TABLE IF EXISTS ' . $this->getTable($stream);
            $this->dbAdatper->getDriver()->getConnection()->execute($streamSql);
        }
        
        $snapshotSql = 'DROP TABLE IF EXISTS snapshot';
        $this->dbAdatper->getDriver()->getConnection()->execute($snapshotSql);
    }
}

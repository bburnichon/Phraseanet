<?php
/**
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\DataStorage;

use Doctrine\DBAL\Connection;

class DbalStorageZoneRepository implements StorageZoneRepository
{
    /**
     * @var \ReflectionProperty[]
     */
    private static $reflectionProperties = [];

    /**
     * @param string $name
     * @return \ReflectionProperty
     */
    private static function getProperty($name)
    {
        if (!isset(self::$reflectionProperties[$name])) {
            $reflectionProperty = new \ReflectionProperty(StorageZone::class, $name);
            $reflectionProperty->setAccessible(true);

            self::$reflectionProperties[$name] = $reflectionProperty;
        }

        return self::$reflectionProperties[$name];
    }

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StorageZone[]
     */
    private $instances = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find($id)
    {
        $id = (int) $id;

        if (!isset($this->instances[$id]) && !array_key_exists($id, $this->instances)) {
            $data = $this->connection->fetchAssoc(
                'SELECT id, type, configuration FROM storage_zones WHERE id = :id',
                ['id' => $id]
            );

            $instance = new StorageZone($data['type'], $data['configuration']);
            self::getProperty('id')->setValue($instance, (int)$data['id']);

            $this->instances[$id] = $instance;
        }

        return $this->instances[$id];
    }

    public function save(StorageZone $zone)
    {
        if ($zone->getId()) {
            $changed = $this->connection->update(
                'storage_zones',
                [
                    'type' => $zone->getType(),
                    'configuration' => $zone->getConfiguration(),
                ],
                ['id' => $zone->getId()]
            );

            return $changed == 1;
        }

        $this->connection->insert(
            'storage_zones',
            [
                'type' => $zone->getType(),
                'configuration' => $zone->getConfiguration(),
            ]
        );

        $id = $this->connection->lastInsertId();

        self::getProperty('id')->setValue($zone, $id);

        return true;
    }

    public function saveMany(array $zones)
    {
        $changed = [];

        foreach ($zones as $key => $zone) {
            $changed[$key] = $this->save($zone);
        }

        return $changed;
    }
}

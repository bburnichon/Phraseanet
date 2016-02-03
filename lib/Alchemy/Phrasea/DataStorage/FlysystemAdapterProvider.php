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

use Assert\Assertion;
use League\Flysystem\AdapterInterface;

class FlysystemAdapterProvider
{
    /**
     * @var \ArrayAccess|array
     */
    private $factories;

    /**
     * @var AdapterInterface[]
     */
    private $adapters = [];

    /**
     * @var KeyProvider
     */
    private $keyProvider;

    public function __construct($factories, KeyProvider $keyProvider = null)
    {
        Assertion::isArrayAccessible($factories);
        $this->factories = $factories;
        $this->keyProvider = $keyProvider ?: new KeyProvider();
    }

    public function getAdapterForStorageZone(StorageZone $storageZone)
    {
        $key = $this->keyProvider->getKeyFor($storageZone);

        if (! isset($this->adapters[$key])) {
            $this->adapters[$key] = $this->createAdapter($storageZone->getType(), $storageZone->getConfiguration());
        }

        return $this->adapters[$key];
    }

    /**
     * @param string $storageType
     * @param array $storageConfiguration
     * @return AdapterInterface
     */
    private function createAdapter($storageType, array $storageConfiguration)
    {
        if (!isset($this->factories[$storageType])) {
            throw new UnknownStorageZoneType($storageType);
        }

        $factory = $this->factories[$storageType];

        if (!is_callable($factory)) {
            throw new \RuntimeException('Expects factory to be callable');
        }

        $adapter = $factory($storageConfiguration);

        if (!$adapter instanceof AdapterInterface) {
            throw new \RuntimeException(sprintf(
                'Expects an instance of "%s", got "%s"',
                AdapterInterface::class,
                is_object($adapter) ? get_class($adapter) : gettype($adapter)
            ));
        }

        return $adapter;
    }
}

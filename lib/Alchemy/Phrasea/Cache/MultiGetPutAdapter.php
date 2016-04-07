<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;

class MultiGetPutAdapter implements Cache, MultiGetCache, MultiPutCache
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    public function getStats()
    {
        return $this->cache->getStats();
    }

    public function fetchMultiple(array $keys)
    {
        if ($this->cache instanceof MultiGetCache) {
            return $this->cache->fetchMultiple($keys);
        }

        $data = [];

        foreach ($keys as $key) {
            $value = $this->fetch($key);

            if (false !== $value || true === $this->contains($key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($this->cache instanceof MultiPutCache) {
            $this->cache->saveMultiple($keysAndValues, $lifetime);
        }

        foreach ($keysAndValues as $key => $value) {
            if (!$this->cache->save($key, $value, $lifetime)) {
                return false;
            }
        }

        return true;
    }
}

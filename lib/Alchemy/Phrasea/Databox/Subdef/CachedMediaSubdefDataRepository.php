<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Databox\Subdef;

use Alchemy\Phrasea\Cache\MultiAdapter;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;

class CachedMediaSubdefDataRepository implements MediaSubdefDataRepository
{
    /**
     * @var MediaSubdefDataRepository
     */
    private $decorated;

    /**
     * @var Cache|MultiGetCache|MultiPutCache
     */
    private $cache;

    /**
     * @var string
     */
    private $baseKey;

    /**
     * @var int
     */
    private $lifeTime = 0;

    /**
     * @param MediaSubdefDataRepository $decorated
     * @param Cache $cache
     * @param string $baseKey
     */
    public function __construct(MediaSubdefDataRepository $decorated, Cache $cache, $baseKey)
    {
        $this->decorated = $decorated;
        $this->cache = $cache instanceof MultiGetCache && $cache instanceof MultiPutCache
            ? $cache
            : new MultiAdapter($cache);
        $this->baseKey = $baseKey;
    }

    /**
     * @return int
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * @param int $lifeTime
     */
    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = (int)$lifeTime;
    }

    /**
     * @param int[] $recordIds
     * @param string[]|null $names
     * @return array
     */
    public function findByRecordIdsAndNames(array $recordIds, array $names = null)
    {
        $keys = $this->generateCacheKeys($recordIds, $names);

        if ($keys) {
            $data = $this->cache->fetchMultiple($keys);

            if (count($keys) === count($data)) {
                return $this->filterNonNull($data);
            }
        }

        return $this->fetchAndSave($recordIds, $names, $keys);
    }

    /**
     * @param array $data
     * @return array
     */
    private function filterNonNull(array $data)
    {
        return array_values(array_filter($data, function ($value) {
            return null !== $value;
        }));
    }

    public function delete(array $subdefIds)
    {
        $deleted = $this->decorated->delete($subdefIds);

        $keys = array_map([$this, 'getCacheKey'], $subdefIds);

        $this->cache->saveMultiple(array_fill_keys($keys, null), $this->lifeTime);

        return $deleted;
    }

    public function save(array $data)
    {
        $this->decorated->save($data);

        $keys = array_map([$this, 'getCacheKey'], $data);

        // all saved keys are now stalled. decorated repository could modify values on store (update time for example)
        array_walk($keys, [$this->cache, 'delete']);
    }

    private function getCacheKey(array $data)
    {
        return $this->baseKey . 'media_subdef=' . json_encode([$data['record_id'], $data['name']]);
    }

    /**
     * @param int[] $recordIds
     * @param string[]|null $names
     * @return string[]
     */
    private function generateCacheKeys(array $recordIds, array $names = null)
    {
        if (null === $names) {
            return [];
        }

        $cacheKeys = [];

        foreach ($recordIds as $recordId) {
            foreach ($names as $name) {
                $cacheKeys[] = [
                    'record_id' => $recordId,
                    'name' => $name,
                ];
            }
        }

        return array_map([$this, 'getCacheKey'], $cacheKeys);
    }

    /**
     * @param int[] $recordIds
     * @param string[]|null $names
     * @param string[] $keys Known keys supposed to be fetched
     * @return array
     */
    private function fetchAndSave(array $recordIds, array $names = null, array $keys = [])
    {
        $retrieved = $this->decorated->findByRecordIdsAndNames($recordIds, $names);

        $data = array_fill_keys($keys, null);

        foreach ($retrieved as $item) {
            $data[$this->getCacheKey($item)] = $item;
        }

        $this->cache->saveMultiple($data, $this->lifeTime);

        return $this->filterNonNull($data);
    }
}

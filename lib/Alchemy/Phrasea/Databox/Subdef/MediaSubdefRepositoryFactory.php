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

use Alchemy\Phrasea\Databox\DataboxBoundRepositoryFactory;
use Alchemy\Phrasea\Databox\DataboxConnectionProvider;
use Doctrine\Common\Cache\Cache;
use Symfony\Component\Stopwatch\Stopwatch;

class MediaSubdefRepositoryFactory implements DataboxBoundRepositoryFactory
{
    /**
     * @var DataboxConnectionProvider
     */
    private $connectionProvider;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var callable
     */
    private $mediaSubdefFactoryProvider;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(DataboxConnectionProvider $connectionProvider, Cache $cache, callable $mediaSubdefFactoryProvider, Stopwatch $stopwatch = null)
    {
        $this->connectionProvider = $connectionProvider;
        $this->cache = $cache;
        $this->mediaSubdefFactoryProvider = $mediaSubdefFactoryProvider;
        $this->stopwatch = $stopwatch;
    }

    public function createRepositoryFor($databoxId)
    {
        $connection = $this->connectionProvider->getConnection($databoxId);

        $dbalRepository = new DbalMediaSubdefDataRepository($connection);
        $dataRepository = new CachedMediaSubdefDataRepository($dbalRepository, $this->cache, sprintf('databox%d:', $databoxId));

        if ($this->stopwatch) {
            $dataRepository = new TraceableMediaSubdefDataRepository($dataRepository, $this->stopwatch);
        }

        $provider = $this->mediaSubdefFactoryProvider;
        $factory = $provider($databoxId);

        if (!is_callable($factory)) {
            throw new \UnexpectedValueException(sprintf(
                'Media subdef factory is expected to be callable, got %s',
                is_object($factory) ? get_class($factory) : gettype($factory)
            ));
        }

        return new MediaSubdefRepository($dataRepository, $factory);
    }
}

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

use Symfony\Component\Stopwatch\Stopwatch;

class TraceableMediaSubdefDataRepository implements MediaSubdefDataRepository
{
    /**
     * @var MediaSubdefDataRepository
     */
    private $repository;
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(MediaSubdefDataRepository $repository, Stopwatch $stopwatch)
    {
        $this->repository = $repository;
        $this->stopwatch = $stopwatch;
    }

    public function findByRecordIdsAndNames(array $recordIds, array $names = null)
    {
        $this->stopwatch->start(__METHOD__, 'data_repository.media_subdef');
        $data = $this->repository->findByRecordIdsAndNames($recordIds, $names);
        $this->stopwatch->stop(__METHOD__);

        return $data;
    }

    public function delete(array $subdefIds)
    {
        $this->stopwatch->start(__METHOD__, 'data_repository.media_subdef');
        $data = $this->repository->delete($subdefIds);
        $this->stopwatch->stop(__METHOD__);

        return $data;
    }

    public function save(array $data)
    {
        $this->stopwatch->start(__METHOD__, 'data_repository.media_subdef');
        $this->repository->save($data);
        $this->stopwatch->stop(__METHOD__);
    }
}

<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Fractal;

use Symfony\Component\Stopwatch\Stopwatch;

class TraceableArraySerializer extends ArraySerializer
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function collection($resourceKey, array $data)
    {
        $this->stopwatch->start(__METHOD__, 'fractal.serializer');
        $serialization = parent::collection($resourceKey, $data);
        $this->stopwatch->stop(__METHOD__);

        return $serialization;
    }

    public function item($resourceKey, array $data)
    {
        $this->stopwatch->start(__METHOD__, 'fractal.serializer');
        $serialization = parent::item($resourceKey, $data);
        $this->stopwatch->stop(__METHOD__);

        return $serialization;
    }

    public function null($resourceKey)
    {
        $this->stopwatch->start(__METHOD__, 'fractal.serializer');
        $serialization = parent::null($resourceKey);
        $this->stopwatch->stop(__METHOD__);

        return $serialization;
    }

}

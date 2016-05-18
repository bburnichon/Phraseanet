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

use League\Fractal\Scope;
use Symfony\Component\Stopwatch\Stopwatch;

class TraceableScope extends Scope
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Scope $scope, Stopwatch $stopwatch)
    {
        parent::__construct($scope->getManager(), $scope->getResource(), $scope->getScopeIdentifier());
        $this->stopwatch = $stopwatch;
    }

    protected function fireTransformer($transformer, $data)
    {
        $this->stopwatch->start(__METHOD__, 'fractal.scope');
        $transform = parent::fireTransformer($transformer, $data);
        $this->stopwatch->stop(__METHOD__);

        return $transform;
    }
}

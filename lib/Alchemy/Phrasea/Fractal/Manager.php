<?php
/**
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Fractal;

use League\Fractal\Manager as BaseManager;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Scope;
use Symfony\Component\Stopwatch\Stopwatch;

class Manager extends BaseManager
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function createData(ResourceInterface $resource, $scopeIdentifier = null, Scope $parentScopeInstance = null)
    {
        return new TraceableScope(parent::createData($resource, $scopeIdentifier, $parentScopeInstance), $this->stopwatch);
    }
}

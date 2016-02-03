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

class UnknownStorageZoneType extends \RuntimeException
{
    public function __construct($type)
    {
        parent::__construct(sprintf('Unknown Storage Zone type: "%s"', $type));
    }
}
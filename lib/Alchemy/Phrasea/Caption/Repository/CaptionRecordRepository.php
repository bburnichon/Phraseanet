<?php
/**
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Caption\Repository;

use Alchemy\Phrasea\Caption\CaptionValue;
use Alchemy\Phrasea\Hydration\IdentityMap;

class CaptionRecordRepository
{
    /**
     * @var DbalCaptionValueRepository
     */
    private $valueRepository;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    public function __construct(DbalCaptionValueRepository $valueRepository, IdentityMap $identityMap)
    {
        $this->valueRepository = $valueRepository;
        $this->identityMap = $identityMap;
    }

    public function findByRecordIds(array $recordIds)
    {
        if (!$recordIds) {
            return [];
        }

        $values = $this->valueRepository->findByRecordIds($recordIds);

        return $this->identityMap->hydrateAll($this->groupByRecordIdAndMetaId($values));
    }

    /**
     * @param CaptionValue[] $values
     * @return array[]
     */
    private function groupByRecordIdAndMetaId($values)
    {
        $captions = [];

        foreach ($values as $value) {
            if (!isset($captions[$value->getRecordId()][$value->getRecordId()])) {
                $captions[$value->getRecordId()][$value->getMetaId()] = [];
            }
            $captions[$value->getRecordId()][$value->getMetaId()][] = $value;
        }

        return $captions;
    }
}

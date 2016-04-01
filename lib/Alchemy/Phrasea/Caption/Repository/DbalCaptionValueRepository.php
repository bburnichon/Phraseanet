<?php
/*
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
use Doctrine\DBAL\Connection;

class DbalCaptionValueRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    public function __construct(Connection $connection, IdentityMap $identityMap)
    {
        $this->connection = $connection;
        $this->identityMap = $identityMap;
    }

    /**
     * @param array $recordIds
     * @return CaptionValue[]
     */
    public function findByRecordIds(array $recordIds)
    {
        if (!$recordIds) {
            return [];
        }

        $data = $this->connection->fetchAll(
            'SELECT id, record_id, meta_struct_id, value, VocabularyType, VocabularyId FROM metadatas WHERE record_id IN (:recordIds)',
            ['recordIds' => $recordIds],
            ['recordIds' => Connection::PARAM_INT_ARRAY]
        );

        return $this->identityMap->hydrateAll($this->normalizeDataForHydration($data));
    }

    /**
     * Normalize data to have proper indexes.
     *
     * This could be done with an array_reduce but is a lot longer and memory intensive
     *
     * @param array[] $data
     * @return array[]
     */
    private function normalizeDataForHydration(array $data)
    {
        return array_combine(
            array_map([$this, 'getIndexFromData'], $data),
            array_map([$this, 'normalizeItem'], $data)
        );
    }

    private function getIndexFromData(array $data)
    {
        return (int)$data['id'];
    }

    private function normalizeItem(array $data)
    {
        return [
            'id' => (int)$data['id'],
            'recordId' => (int)$data['record_id'],
            'metaId' => (int)$data['meta_struct_id'],
            'value' => $data['value'],
            'vocabularyType' => $data['VocabularyType'],
            'vocabularyId' => (int)$data['VocabularyId'],
        ];
    }
}

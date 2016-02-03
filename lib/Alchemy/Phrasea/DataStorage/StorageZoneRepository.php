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

interface StorageZoneRepository
{
    /**
     * @param int $id
     * @return StorageZone|null
     */
    public function find($id);

    /**
     * @param StorageZone $zone
     * @return bool
     */
    public function save(StorageZone $zone);

    /**
     * @param StorageZone[] $zones
     * @return bool[]
     */
    public function saveMany(array $zones);
}

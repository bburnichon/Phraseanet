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

use Alchemy\Phrasea\Databox\DataboxConnectionProvider;

class DbalStorageZoneRepositoryFactory implements StorageZoneRepositoryFactory
{
    /**
     * @var DataboxConnectionProvider
     */
    private $connectionProvider;

    public function __construct(DataboxConnectionProvider $connectionProvider)
    {
        $this->connectionProvider = $connectionProvider;
    }

    public function createRepositoryForDatabox($databoxId)
    {
        $connection = $this->connectionProvider->getConnection($databoxId);

        return new DbalStorageZoneRepository($connection);
    }
}

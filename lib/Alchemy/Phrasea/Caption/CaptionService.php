<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Caption;

use Alchemy\Phrasea\Databox\DataboxBoundRepositoryProvider;
use Alchemy\Phrasea\Record\RecordReferenceCollection;

class CaptionService
{
    /**
     * @var DataboxBoundRepositoryProvider
     */
    private $repositoryProvider;

    public function __construct(DataboxBoundRepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    public function fetchByRecordIds($records)
    {
        if (!$records instanceof RecordReferenceCollection) {
            $records = new RecordReferenceCollection($records);
        }

        foreach ($records->groupPerDataboxId() as $databoxId => $indexes) {

        }
    }
}

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

class CaptionRecord
{
    /**
     * @var \databox_descriptionStructure
     */
    private $structure;

    /**
     * @var int
     */
    private $recordId;

    /**
     * @param \databox_descriptionStructure $structure
     * @param int $recordId
     */
    public function __construct(\databox_descriptionStructure $structure, $recordId)
    {
        $this->structure = $structure;
        $this->recordId = $recordId;
    }

    /**
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }
}

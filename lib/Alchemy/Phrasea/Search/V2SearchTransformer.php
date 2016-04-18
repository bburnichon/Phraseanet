<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Search;

use Alchemy\Phrasea\Model\RecordInterface;
use League\Fractal\TransformerAbstract;

class V2SearchTransformer extends TransformerAbstract
{
    public function transform(SearchResultView $searchView)
    {
        return [
            'offset_start' => $searchView->getResult()->getOptions()->getFirstResult(),
            'per_page' => $searchView->getResult()->getOptions()->getMaxResults(),
            'available_results' => $searchView->getResult()->getAvailable(),
            'total_results' => $searchView->getResult()->getTotal(),
            'error' => (string)$searchView->getResult()->getError(),
            'warning' => (string)$searchView->getResult()->getWarning(),
            'query_time' => $searchView->getResult()->getDuration(),
            'search_indexes' => $searchView->getResult()->getIndexes(),
            'facets' => $searchView->getResult()->getFacets(),
            'search_type' => $searchView->getResult()->getOptions()->getSearchType(),
            'results' => $this->listResults($searchView->getResult()->getResults()),
        ];
    }

    /**
     * @param RecordInterface[] $results
     * @return array
     */
    public function listResults($results)
    {
        $data = [];

        foreach ($results as $record) {
            $data[] = [
                'databox_id' => $record->getDataboxId(),
                'record_id' => $record->getRecordId(),
                'collection_id' => $record->getCollectionId(),
                'version' => $record->getUpdated()->getTimestamp(),
            ];
        }

        return $data;
    }
}

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

class KeyProvider
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * @param StorageZone $storageZone
     * @return string
     */
    public function getKeyFor(StorageZone $storageZone)
    {
        $sortedKey = $this->recursiveArraySort($storageZone->getConfiguration());

        return $this->prefix . $storageZone->getType() . '_' . sha1(json_encode($sortedKey));
    }

    /**
     * Returns a recursively sorted array by keys
     * @param array $array
     * @return array
     */
    private function recursiveArraySort(array $array)
    {
        ksort($array);

        foreach ($array as $key => &$item) {
            if (is_array($item)) {
                $item = $this->recursiveArraySort($item);
            }
        }

        return $array;
    }
}

<?php
/**
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Alchemy\Phrasea\Application;
use Doctrine\DBAL\Connection;

class patch_400alpha6a implements patchInterface
{
    /** @var string */
    private $release = '4.0.0-alpha.6';

    /** @var array */
    private $concern = [base::DATA_BOX];

    /**
     * {@inheritdoc}
     */
    public function get_release()
    {
        return $this->release;
    }

    /**
     * {@inheritdoc}
     */
    public function getDoctrineMigrations()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function require_all_upgrades()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function concern()
    {
        return $this->concern;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(base $databox, Application $app)
    {
        /** @var databox $databox */
        $domStructure = $databox->get_dom_structure();

        $xpath = new DOMXPath($domStructure);
        $pathNodes = $xpath->query('/record/path|/record/subdefs//path');

        $zones = $this->insertStorageZonesIntoDatabox($databox->get_connection(), $pathNodes);

        foreach ($zones as $zone) {
            $storageNode = $domStructure->createElement('storage-zone-id');
            $storageNode->appendChild($domStructure->createTextNode($zone['id']));

            $pathNode = $zone['node'];

            $pathNode->parentNode->replaceChild($storageNode, $pathNode);
        }

        $databox->saveStructure($domStructure);

        $result = $databox->get_connection()->fetchAll('SELECT path FROM subdef WHERE storage_zone_id = 0 LIMIT 1');
        while (!empty($result)) {
            preg_match('#^/|^[A-Za-z]:[/\\\\]#u', $result[0]['path'], $matches);

            $zone = $this->insertZone($databox->get_connection(), $matches[0]);
            $databox->get_connection()->executeUpdate(
                'UPDATE subdef SET storage_zone_id = :storage_zone_id, path = SUBSTRING(path, 1 + LENGTH(:prefix))'
                . ' WHERE storage_zone_id = 0 AND LEFT(path, LENGTH(:prefix)) = :prefix',
                [
                    'storage_zone_id' => $zone['id'],
                    'prefix' => $matches[0],
                ]
            );
            $result = $databox->get_connection()->fetchAll('SELECT path FROM subdef WHERE storage_zone_id = 0 LIMIT 1');
        }

        return true;
    }

    /**
     * @param Connection $connection
     * @param DOMNodeList $pathNodes
     * @return array
     */
    private function insertStorageZonesIntoDatabox(Connection $connection, DOMNodeList $pathNodes)
    {
        $statement = $connection->prepare(
            'UPDATE subdef SET storage_zone_id = :storage_zone_id, path = SUBSTRING(path, 1 + LENGTH(:prefix))'
            . ' WHERE name = :name AND storage_zone_id = 0 AND LEFT(path, LENGTH(:prefix)) = :prefix'
        );

        $zones = [];

        foreach ($pathNodes as $pathNode) {
            $path = $pathNode->nodeValue;

            $zone = $this->insertZone($connection, $path);
            $statement->execute([
                'storage_zone_id' => $zone['id'],
                'name' => $this->getSubdefName($pathNode),
                'prefix' => rtrim($path, '/\\') . DIRECTORY_SEPARATOR,
            ]);

            $zone['node'] = $pathNode;
            $zones[] = $zone;
        }

        return $zones;
    }

    /**
     * @param DOMElement $pathNode
     * @return string
     */
    private function getSubdefName($pathNode)
    {
        if ('record' === $pathNode->parentNode->nodeName) {
            return 'document';
        } elseif ('subdef' === $pathNode->parentNode->nodeName) {
            return $pathNode->parentNode->getAttribute('name');
        }

        return 'unknown';
    }

    /**
     * @param Connection $connection
     * @param $path
     * @return array
     */
    private function insertZone(Connection $connection, $path)
    {
        $zone = [
            'type' => 'local',
            'configuration' => json_encode(['path' => $path]),
        ];

        $connection->insert('storage_zones', $zone);
        $zone['id'] = $connection->lastInsertId();

        return $zone;
    }
}

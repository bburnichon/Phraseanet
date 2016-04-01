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

use Alchemy\Phrasea\Vocabulary\ControlProvider\ControlProviderInterface;

class CaptionValue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $recordId;

    /**
     * @var int
     */
    private $metaId;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string|null
     */
    private $vocabularyType;

    /**
     * @var mixed|null
     */
    private $vocabularyId;

    /**
     * @param int $recordId
     * @param int $metaId
     * @param string $value
     */
    public function __construct($recordId, $metaId, $value)
    {
        $this->recordId = $recordId;
        $this->metaId = $metaId;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * @return int
     */
    public function getMetaId()
    {
        return $this->metaId;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->vocabularyType = null;
        $this->vocabularyId = null;

        $this->value = (string)$value;
    }

    /**
     * @param ControlProviderInterface $vocabulary
     * @param mixed $vocabularyId
     */
    public function setValueFromVocabulary(ControlProviderInterface $vocabulary, $vocabularyId)
    {
        $this->vocabularyType = $vocabulary->getType();
        $this->vocabularyId = $vocabularyId;

        $this->value = $vocabulary->getValue($this->vocabularyId);
    }

    /**
     * @return bool
     */
    public function isVocabularyBound()
    {
        return $this->vocabularyType !== null;
    }

    /**
     * @return string
     */
    public function getVocabularyType()
    {
        return $this->vocabularyType;
    }

    /**
     * @return mixed
     */
    public function getVocabularyId()
    {
        return $this->vocabularyId;
    }
}

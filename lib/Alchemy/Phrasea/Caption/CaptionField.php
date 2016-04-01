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

class CaptionField
{
    /**
     * @var \databox_field
     */
    private $field;
    /**
     * @var CaptionValue
     */
    private $value;

    public function __construct(\databox_field $field, CaptionValue $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @return \databox_field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return CaptionValue
     */
    public function getValue()
    {
        return $this->value;
    }
}

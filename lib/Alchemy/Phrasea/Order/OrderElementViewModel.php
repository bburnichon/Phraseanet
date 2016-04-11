<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2016 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Order;

use Alchemy\Phrasea\Model\Entities\OrderElement;
use Alchemy\Phrasea\Model\Entities\User;
use Alchemy\Phrasea\Model\RecordReferenceInterface;
use Assert\Assertion;

class OrderElementViewModel
{
    /**
     * @var OrderElement
     */
    private $element;

    /**
     * @var RecordReferenceInterface
     */
    private $record;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \media_subdef[]
     */
    private $subdefs = [];

    /**
     * OrderElementViewModel constructor.
     * @param OrderElement $element
     * @param RecordReferenceInterface $record
     * @param User $user
     */
    public function __construct(OrderElement $element, RecordReferenceInterface $record, User $user)
    {
        $this->element = $element;
        $this->record = $record;
        $this->user = $user;
    }

    public function getElement()
    {
        return $this->element;
    }

    public function getRecordReference()
    {
        return $this->record;
    }

    public function getAuthenticatedUser()
    {
        return $this->user;
    }

    /**
     * @param SubdefViewModel[] $subdefs
     */
    public function setOrderableMediaSubdefs($subdefs)
    {
        Assertion::allIsInstanceOf($subdefs, SubdefViewModel::class);

        $this->subdefs = $subdefs instanceof \Traversable ? iterator_to_array($subdefs) : $subdefs;
    }

    public function getOrderableMediaSubdefs()
    {
        return $this->subdefs;
    }
}

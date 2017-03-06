<?php

namespace Toro\Bundle\GeoBundle\Model;

use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ZoneMemberInterface extends ResourceInterface, CodeAwareInterface
{
    /**
     * @return ZoneInterface
     */
    public function getBelongsTo();

    /**
     * @param ZoneInterface $belongsTo
     */
    public function setBelongsTo(ZoneInterface $belongsTo = null);
}

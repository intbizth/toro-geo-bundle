<?php

namespace Toro\Bundle\GeoBundle\Factory;

use Sylius\Component\Resource\Factory\FactoryInterface;
use Toro\Bundle\GeoBundle\Model\ZoneInterface;

interface ZoneFactoryInterface extends FactoryInterface
{
    /**
     * @param string $type
     *
     * @return ZoneInterface
     */
    public function createTyped($type);

    /**
     * @param array $membersCodes
     *
     * @return ZoneInterface
     */
    public function createWithMembers(array $membersCodes);
}

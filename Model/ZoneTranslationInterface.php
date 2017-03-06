<?php

namespace Toro\Bundle\GeoBundle\Model;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ZoneTranslationInterface extends ResourceInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

}

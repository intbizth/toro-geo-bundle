<?php

namespace Toro\Bundle\GeoBundle\Model;

use Sylius\Component\Resource\Model\ResourceInterface;

interface GeoNameTranslationInterface extends ResourceInterface
{
    public function __toString();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getAbbreviation();

    /**
     * @param string $abbreviation
     */
    public function setAbbreviation($abbreviation);

    /**
     * @return string
     */
    public function getGeoName();

    /**
     * @param string $geoName
     */
    public function setGeoName($geoName);
}

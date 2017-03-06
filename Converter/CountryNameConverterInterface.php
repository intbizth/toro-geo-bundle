<?php

namespace Toro\Bundle\GeoBundle\Converter;

interface CountryNameConverterInterface
{
    /**
     * @param string $name
     * @param string $locale
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function convertToCode($name, $locale = 'th');
}

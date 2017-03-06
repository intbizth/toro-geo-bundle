<?php

namespace Toro\Bundle\GeoBundle\Converter;

use Symfony\Component\Intl\Intl;

final class CountryNameConverter implements CountryNameConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convertToCode($name, $locale = 'en')
    {
        $names = Intl::getRegionBundle()->getCountryNames($locale);
        $countryCode = array_search($name, $names, true);

        if (false === $countryCode) {
            throw new \InvalidArgumentException(sprintf(
                'Country "%s" not found! Available names: %s.', $name, implode(', ', $names)
            ));
        }

        return $countryCode;
    }
}

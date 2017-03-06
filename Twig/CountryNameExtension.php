<?php

namespace Toro\Bundle\GeoBundle\Twig;

use Symfony\Component\Intl\Intl;
use Toro\Bundle\GeoBundle\Model\CountryInterface;

class CountryNameExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('country_name', [$this, 'translateCountryIsoCode']),
        ];
    }

    /**
     * @param mixed  $country
     * @param string $locale
     *
     * @return string
     */
    public function translateCountryIsoCode($country, $locale = null)
    {
        if ($country instanceof CountryInterface) {
            return Intl::getRegionBundle()->getCountryName($country->getCode(), $locale);
        }

        return Intl::getRegionBundle()->getCountryName($country, $locale);
    }
}

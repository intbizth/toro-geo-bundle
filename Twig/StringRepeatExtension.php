<?php

namespace Toro\Bundle\GeoBundle\Twig;

class StringRepeatExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('repeat', 'str_repeat'),
        ];
    }
}

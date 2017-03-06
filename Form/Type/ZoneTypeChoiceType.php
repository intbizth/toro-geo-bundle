<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Toro\Bundle\GeoBundle\Model\ZoneInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ZoneTypeChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'toro.form.zone.types.country' => ZoneInterface::TYPE_COUNTRY,
                    'toro.form.zone.types.province' => ZoneInterface::TYPE_PROVINCE,
                    'toro.form.zone.types.zone' => ZoneInterface::TYPE_ZONE,
                ],
                'label' => 'toro.form.zone.type',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_zone_type_choice';
    }
}

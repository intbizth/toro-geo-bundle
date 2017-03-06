<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class GeoNameTranslationType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'toro.form.geo_name.name',]
            ])
            ->add('abbreviation', TextType::class, [
                'label' => false,
                'attr' => ['placeholder' => 'toro.form.geo_name.abbreviation',]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_geo_name_translation';
    }
}

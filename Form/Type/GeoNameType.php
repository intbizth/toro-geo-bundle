<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Toro\Bundle\GeoBundle\Form\EventListener\BuildGeoNameFormSubscriber;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;

final class GeoNameType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'toro.form.geo_name.type',
                'choices' => array_flip([
                    GeoNameInterface::TYPE_PROVINCE => 'toro.form.geo_name.types.province',
                    GeoNameInterface::TYPE_DISTRICT => 'toro.form.geo_name.types.district',
                    GeoNameInterface::TYPE_SUB_DISTRICT => 'toro.form.geo_name.types.sub_district',
                ])
            ])
            ->add('postcode', IntegerType::class, [
                'label' => 'toro.form.geo_name.postcode',
                'required' => false,
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => GeoNameTranslationType::class,
                'label' => 'toro.form.geo_name.name',
            ])
            ->addEventSubscriber(new BuildGeoNameFormSubscriber($builder))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_geo_name';
    }
}

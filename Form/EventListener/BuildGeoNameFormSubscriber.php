<?php

namespace Toro\Bundle\GeoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Toro\Bundle\AdminBundle\Form\ChoiceList\ChoiceResizeListener;
use Toro\Bundle\GeoBundle\Form\Type\GeoNameChoiceType;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;

/**
 * @internal
 */
final class BuildGeoNameFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormBuilderInterface
     */
    private $builder;

    /**
     * @param FormBuilderInterface $builder
     */
    public function __construct(FormBuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $geoName = $event->getData();

        if (null === $geoName) {
            return;
        }

        $factory = $this->builder->getFormFactory();
        $event->getForm()->add($factory->createNamed('parent', GeoNameChoiceType::class, $geoName->getParent(), $options = [
            'filter' => $this->getFilterGeoNameOption($geoName),
            'required' => false,
            'label' => 'toro.form.geo_name.parent',
            'placeholder' => '---',
            'auto_initialize' => false,
            'choices' => [],
            'choice_label' => 'geoName',
        ]));

        ChoiceResizeListener::create($this->builder, [
            'parent' => [
                'entry_type' => GeoNameChoiceType::class,
                'options' => $options,
                'query_builder' => 'o.id',
            ]
        ]);
    }

    /**
     * @param GeoNameInterface $geoName
     *
     * @return callable|null
     */
    private function getFilterGeoNameOption(GeoNameInterface $geoName)
    {
        if (null === $geoName->getId()) {
            return null;
        }

        return function (GeoNameInterface $entry) use ($geoName) {
            return $entry->getId() !== $geoName->getId();
        };
    }
}

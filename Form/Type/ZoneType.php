<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Toro\Bundle\GeoBundle\Model\ZoneInterface;

final class ZoneType extends AbstractResourceType
{
    /**
     * @var array
     */
    private $scopeChoices;

    /**
     * @param string   $dataClass
     * @param string[] $validationGroups
     * @param string[] $scopeChoices
     */
    public function __construct($dataClass, array $validationGroups, array $scopeChoices = [])
    {
        parent::__construct($dataClass, $validationGroups);

        $this->scopeChoices = $scopeChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('type', ZoneTypeChoiceType::class, [
                'disabled' => true,
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'entry_type' => ZoneTranslationType::class,
                'label' => 'toro.form.zone.name',
            ])
        ;

        if (!empty($this->scopeChoices)) {
            $builder
                ->add('scope', ChoiceType::class, [
                    'choices' => array_flip($this->scopeChoices),
                    'label' => 'toro.form.zone.scope',
                    'placeholder' => 'toro.form.zone.select_scope',
                    'required' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ZoneInterface $zone */
            $zone = $event->getData();

            $event->getForm()->add('members', CollectionType::class, [
                'entry_type' => ZoneMemberType::class,
                'entry_options' => [
                    'entry_type' => $this->getZoneMemberEntryType($zone->getType()),
                    'entry_options' => $this->getZoneMemberEntryOptions($zone->getType()),
                ],
                'button_add_label' => 'toro.form.zone.add_member',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => true,
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_zone';
    }

    /**
     * @param string $zoneMemberType
     *
     * @return string
     */
    private function getZoneMemberEntryType($zoneMemberType)
    {
        $zoneMemberEntryTypes = [
            ZoneInterface::TYPE_COUNTRY => CountryCodeChoiceType::class,
            ZoneInterface::TYPE_PROVINCE => GeoNameCodeChoiceType::class,
            ZoneInterface::TYPE_ZONE => ZoneCodeChoiceType::class,
        ];

        return $zoneMemberEntryTypes[$zoneMemberType];
    }

    /**
     * @param string $zoneMemberType
     *
     * @return array
     */
    private function getZoneMemberEntryOptions($zoneMemberType)
    {
        $zoneMemberEntryOptions = [
            ZoneInterface::TYPE_COUNTRY => ['label' => 'toro.form.zone.types.country'],
            ZoneInterface::TYPE_PROVINCE => ['label' => 'toro.form.zone.types.province'],
            ZoneInterface::TYPE_ZONE => ['label' => 'toro.form.zone.types.zone'],
        ];

        return $zoneMemberEntryOptions[$zoneMemberType];
    }
}

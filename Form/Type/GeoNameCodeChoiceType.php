<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GeoNameCodeChoiceType extends AbstractType
{
    /**
     * @var RepositoryInterface
     */
    private $geoNameRepository;

    /**
     * @param RepositoryInterface $geoNameRepository
     */
    public function __construct(RepositoryInterface $geoNameRepository)
    {
        $this->geoNameRepository = $geoNameRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ReversedTransformer(new ResourceToIdentifierTransformer($this->geoNameRepository, 'code')));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return GeoNameChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_geo_name_code_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choice_value' => 'code',
            ])
        ;
    }
}

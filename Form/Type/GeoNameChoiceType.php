<?php

namespace Toro\Bundle\GeoBundle\Form\Type;

use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Toro\Bundle\GeoBundle\Repository\GeoNameRepositoryInterface;

final class GeoNameChoiceType extends AbstractType
{
    /**
     * @var GeoNameRepositoryInterface
     */
    private $geoNameRepository;

    /**
     * @param GeoNameRepositoryInterface $geoNameRepository
     */
    public function __construct(GeoNameRepositoryInterface $geoNameRepository)
    {
        $this->geoNameRepository = $geoNameRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ('geoName' === $options['choice_label']) {
            return;
        }

        /** @var ChoiceView $choice */
        foreach ($view->vars['choices'] as $choice) {
            $choice->label = str_repeat('— ', $choice->data->getLevel()).$choice->label;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => function (Options $options) {
                    return $this->getTaxons($options['root_code'], $options['filter']);
                },
                'choice_value' => 'id',
                'choice_label' => 'name',
                'choice_translation_domain' => false,
                'root_code' => null,
                'filter' => null,
                'class' => $this->geoNameRepository->getClassName(),
            ])
            ->setAllowedTypes('root_code', ['string', 'null'])
            ->setAllowedTypes('filter', ['callable', 'null'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'toro_geo_name_choice';
    }

    /**
     * @param string|null $rootCode
     * @param callable|null $filter
     *
     * @return TaxonInterface[]
     */
    private function getTaxons($rootCode = null, $filter = null)
    {
        $geoNames = $this->geoNameRepository->findNodesTreeSorted($rootCode);

        if (null !== $filter) {
            $geoNames = array_filter($geoNames, $filter);
        }

        return $geoNames;
    }
}

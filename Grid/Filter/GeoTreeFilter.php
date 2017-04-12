<?php

namespace Toro\Bundle\GeoBundle\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;

class GeoTreeFilter implements FilterInterface
{
    const NAME = 'geo_tree';

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var FilterInterface
     */
    private $stringFilter;

    /**
     * @var FilterInterface
     */
    private $multiStringFilter;

    public function __construct(RepositoryInterface $repository, FilterInterface $stringFilter, FilterInterface $multiStringFilter = null)
    {
        $this->repository = $repository;
        $this->stringFilter = $stringFilter;
        $this->multiStringFilter = $multiStringFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options)
    {
        if (!$data instanceof GeoNameInterface) {
            $type = 'contains';

            if (is_array($data)) {
                $type = @$data['type'];
                $data = @$data['value'];
            }

            if (is_numeric($data)) {
                $data = $this->repository->find($data);
            }

            if (is_string($data)) {
                return ($this->multiStringFilter ?: $this->stringFilter)
                    ->apply($dataSource, $name, ['value' => $data, 'type' => $type], array_replace_recursive([
                        'fields' => [$options['trans']]
                    ], $options))
                ;
            }

            if (!$data instanceof GeoNameInterface) {
                throw new \InvalidArgumentException("Requred for type: " . GeoNameInterface::class);
            }
        }

        $expr = $dataSource->getExpressionBuilder();
        $fields = array_key_exists('fields', $options) ? $options['fields'] : [$name];
        $field = current($fields);

        $expr->addOrderBy($options['alias'].'.root', 'ASC');
        $expr->addOrderBy($options['alias'].'.left', 'ASC');

        $dataSource->restrict($expr->andX(
            $expr->greaterThanOrEqual($options['alias'].'.left', $data->getLeft()),
            $expr->lessThanOrEqual($options['alias'].'.right', $data->getRight())
        ));
    }
}

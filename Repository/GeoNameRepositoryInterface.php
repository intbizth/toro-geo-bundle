<?php

namespace Toro\Bundle\GeoBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;

interface GeoNameRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $parentCode
     * @param string $locale
     *
     * @return GeoNameInterface[]
     */
    public function findChildren($parentCode, $locale);

    /**
     * @return GeoNameInterface[]
     */
    public function findRootNodes();

    /**
     * @param string|null $rootCode
     *
     * @return GeoNameInterface[]
     */
    public function findNodesTreeSorted($rootCode = null);

    /**
     * @param string $name
     * @param string $locale
     *
     * @return GeoNameInterface[]
     */
    public function findByName($name, $locale);

    /**
     * @param string $locale
     *
     * @return QueryBuilder
     */
    public function createListQueryBuilder($locale);

    /**
     * @return GeoNameInterface[]
     */
    public function findProvinces();

    /**
     * @param $name
     * @param string|null $locale
     *
     * @return null|GeoNameInterface
     */
    public function findOneByName($name, $locale = null);

    /**
     * @param string $name
     * @param string|null $locale
     *
     * @return null|GeoNameInterface
     */
    public function findOneBySlug($name, $locale = null);
}

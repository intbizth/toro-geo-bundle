<?php

namespace Toro\Bundle\GeoBundle\Doctrine\ORM;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Toro\Bundle\GeoBundle\Repository\GeoNameRepositoryInterface;

class GeoNameRepository extends EntityRepository implements GeoNameRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findChildren($parentCode, $locale)
    {
        return $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->addSelect('child')
            ->innerJoin('o.parent', 'parent')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->leftJoin('o.children', 'child')
            ->andWhere('parent.code = :parentCode')
            ->setParameter('parentCode', $parentCode)
            ->setParameter('locale', $locale)
            ->addOrderBy('o.left')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name, $locale)
    {
        return $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation')
            ->andWhere('translation.name = :name')
            ->andWhere('translation.locale = :locale')
            ->setParameter('name', $name)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findRootNodes()
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.parent IS NULL')
            ->addOrderBy('o.left')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findNodesTreeSorted($rootCode = null)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        if (null !== $rootCode) {
            $queryBuilder
                ->join('o.root', 'root')
                ->andWhere('root.code = :rootCode')
                ->setParameter('rootCode', $rootCode)
            ;
        }

        return $queryBuilder
            ->addOrderBy('o.root')
            ->addOrderBy('o.left')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function createFilterQueryBuilder($locale, $rootCode = null)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        if ($rootCode) {
            $queryBuilder
                ->innerJoin('o.root', 'root')
                ->andWhere('root.code = :rootCode')
                ->setParameter('rootCode', $rootCode)
            ;
        }

        return $queryBuilder
            ->addSelect('translation')
            ->addSelect('parent')
            ->innerJoin('o.parent', 'parent')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere($queryBuilder->expr()->between('o.left', 'parent.left', 'parent.right'))
            ->setParameter('locale', $locale)
            ->addOrderBy('o.left')
            ;
    }
}

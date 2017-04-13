<?php

namespace Toro\Bundle\GeoBundle\Doctrine\ORM;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;
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
        $queryBuilder = $this->createQueryBuilder('o')
            ->addOrderBy('o.root')
            ->addOrderBy('o.left')

            ->addSelect('t')
            ->join('o.translations', 't')
        ;

        if (null !== $rootCode) {
            $queryBuilder
                ->join('o.root', 'root')
                ->andWhere('root.code = :rootCode')
                ->setParameter('rootCode', $rootCode)
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function createListQueryBuilder($locale)
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
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

    /**
     * {@inheritdoc}
     */
    public function findProvinces()
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation')
            ->where('o.type = :type')
            ->setParameter('type', GeoNameInterface::TYPE_PROVINCE)
        ;

        return $queryBuilder
            ->addOrderBy('translation.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByName($name)
    {
        $queryBuilder = $this->createQueryBuilder('o')
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation')
            ->where('translation.name = :name')
            ->setParameter('name', $name)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}

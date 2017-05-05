<?php

namespace Toro\Bundle\GeoBundle\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;

interface GeoNameInterface extends CodeAwareInterface, GeoNameTranslationInterface, TranslatableInterface
{
    const TYPE_PROVINCE = 1;
    const TYPE_DISTRICT = 2;
    const TYPE_SUB_DISTRICT = 3;

    /**
     * @return int
     */
    public function getPostcode();

    /**
     * @param int $postcode
     */
    public function setPostcode($postcode);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     */
    public function setType($type);

    /**
     * @return CountryInterface
     */
    public function getCountry();

    /**
     * @param CountryInterface $country
     */
    public function setCountry(CountryInterface $country = null);

    /**
     * @return boolean
     */
    public function isProvince();

    /**
     * @return boolean
     */
    public function isDistrict();

    /**
     * @return boolean
     */
    public function isSubDistrict();

    /**
     * @return bool
     */
    public function isRoot();

    /**
     * @return GeoNameInterface
     */
    public function getRoot();

    /**
     * @return GeoNameInterface
     */
    public function getParent();

    /**
     * @param null|GeoNameInterface $parent
     */
    public function setParent(GeoNameInterface $parent = null);

    /**
     * @return GeoNameInterface[]
     */
    public function getParents();

    /**
     * @return Collection|GeoNameInterface[]
     */
    public function getChildren();

    /**
     * @param GeoNameInterface $child
     *
     * @return bool
     */
    public function hasChild(GeoNameInterface $child);

    /**
     * @param GeoNameInterface $child
     */
    public function addChild(GeoNameInterface $child);

    /**
     * @param GeoNameInterface $child
     */
    public function removeChild(GeoNameInterface $child);

    /**
     * @return int
     */
    public function getLeft();

    /**
     * @param int $left
     */
    public function setLeft($left);

    /**
     * @return int
     */
    public function getRight();

    /**
     * @param int $right
     */
    public function setRight($right);

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @param int $level
     */
    public function setLevel($level);

    /**
     * @deprecated use getGeoAddress
     *
     * @return string
     */
    public function getAddressName();

    /**
     * @return string
     */
    public function getGeoAddress();
}

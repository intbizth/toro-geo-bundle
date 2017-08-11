<?php

namespace Toro\Bundle\GeoBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Symfony\Component\Intl\Intl;

class Country implements CountryInterface
{
    use ToggleableTrait;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * Country code ISO 3166-1 alpha-2.
     *
     * @var string
     */
    protected $code;

    /**
     * @var Collection|GeoNameInterface[]
     */
    protected $provinces;

    public function __construct()
    {
        $this->provinces = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($locale = null)
    {
        return Intl::getRegionBundle()->getCountryName($this->code, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvinces()
    {
        return $this->provinces;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProvinces()
    {
        return !$this->provinces->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function addProvince(GeoNameInterface $province)
    {
        if (!$province->isProvince()) {
            return;
        }

        if (!$this->hasProvince($province)) {
            $this->provinces->add($province);
            $province->setCountry($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeProvince(GeoNameInterface $province)
    {
        if ($this->hasProvince($province)) {
            $this->provinces->removeElement($province);
            $province->setCountry(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasProvince(GeoNameInterface $province)
    {
        return $this->provinces->contains($province);
    }
}

<?php

namespace Toro\Bundle\GeoBundle\Model;

use Sylius\Component\Resource\Model\AbstractTranslation;

class ZoneTranslation extends AbstractTranslation implements ZoneTranslationInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

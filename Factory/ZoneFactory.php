<?php

namespace Toro\Bundle\GeoBundle\Factory;

use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Toro\Bundle\GeoBundle\Model\ZoneInterface;
use Toro\Bundle\GeoBundle\Model\ZoneMemberInterface;

final class ZoneFactory implements ZoneFactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var FactoryInterface
     */
    private $zoneMemberFactory;

    /**
     * @param FactoryInterface $factory
     * @param FactoryInterface $zoneMemberFactory
     */
    public function __construct(FactoryInterface $factory, FactoryInterface $zoneMemberFactory)
    {
        $this->factory = $factory;
        $this->zoneMemberFactory = $zoneMemberFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedTypeException
     */
    public function createNew()
    {
        return $this->factory->createNew();
    }

    /**
     * {@inheritdoc}
     */
    public function createTyped($type)
    {
        /* @var ZoneInterface $zone */
        $zone = $this->createNew();
        $zone->setType($type);

        return $zone;
    }

    /**
     * {@inheritdoc}
     */
    public function createWithMembers(array $membersCodes)
    {
        /* @var ZoneInterface $zone */
        $zone = $this->createNew();

        foreach ($membersCodes as $memberCode) {
            /** @var ZoneMemberInterface $zoneMember */
            $zoneMember = $this->zoneMemberFactory->createNew();
            $zoneMember->setCode($memberCode);

            $zone->addMember($zoneMember);
        }

        return $zone;
    }
}

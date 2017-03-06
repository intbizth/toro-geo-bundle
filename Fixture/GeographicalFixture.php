<?php

namespace Toro\Bundle\GeoBundle\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Intl\Intl;
use Toro\Bundle\GeoBundle\Factory\ZoneFactoryInterface;
use Toro\Bundle\GeoBundle\Model\CountryInterface;
use Toro\Bundle\GeoBundle\Model\GeoNameInterface;
use Toro\Bundle\GeoBundle\Model\ZoneInterface;

class GeographicalFixture extends AbstractFixture
{
    /**
     * @var FactoryInterface
     */
    private $countryFactory;

    /**
     * @var ObjectManager
     */
    private $countryManager;

    /**
     * @var FactoryInterface
     */
    private $provinceFactory;

    /**
     * @var ObjectManager
     */
    private $provinceManager;

    /**
     * @var ZoneFactoryInterface
     */
    private $zoneFactory;

    /**
     * @var ObjectManager
     */
    private $zoneManager;

    /**
     * @var RepositoryInterface
     */
    private $geoNameRepository;

    /**
     * @param FactoryInterface $countryFactory
     * @param ObjectManager $countryManager
     * @param FactoryInterface $provinceFactory
     * @param ObjectManager $provinceManager
     * @param ZoneFactoryInterface $zoneFactory
     * @param ObjectManager $zoneManager
     * @param RepositoryInterface $geoNameRepository
     */
    public function __construct(
        FactoryInterface $countryFactory,
        ObjectManager $countryManager,
        FactoryInterface $provinceFactory,
        ObjectManager $provinceManager,
        ZoneFactoryInterface $zoneFactory,
        ObjectManager $zoneManager,
        RepositoryInterface $geoNameRepository
    ) {
        $this->countryFactory = $countryFactory;
        $this->countryManager = $countryManager;
        $this->provinceFactory = $provinceFactory;
        $this->provinceManager = $provinceManager;
        $this->zoneFactory = $zoneFactory;
        $this->zoneManager = $zoneManager;
        $this->geoNameRepository = $geoNameRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $options)
    {
        $this->loadCountriesWithProvinces($options['provinces']);

        $this->countryManager->flush();
        $this->provinceManager->flush();

        $this->loadZones($options['zones']);

        $this->zoneManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'geographical';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode)
    {
        $optionsNodeBuilder = $optionsNode->children();

        /** @var ArrayNodeDefinition $provinceNode */
        $optionsNodeBuilder
            ->variableNode('provinces')
        ;

        /** @var ArrayNodeDefinition $zoneNode */
        $zoneNode = $optionsNodeBuilder
            ->arrayNode('zones')
            ->normalizeKeys(false)
            ->useAttributeAsKey('code')
            ->prototype('array')
        ;

        $zoneNode
            ->performNoDeepMerging()
            ->children()
            ->scalarNode('name')->cannotBeEmpty()->end()
            ->arrayNode('countries')->prototype('scalar')->end()->end()
            ->arrayNode('zones')->prototype('scalar')->end()->end()
            ->arrayNode('provinces')->prototype('scalar')->end()->end()
            ->scalarNode('scope')->end()
        ;

        $zoneNode
            ->validate()
            ->ifTrue(function ($zone) {
                $filledTypes = 0;
                $filledTypes += empty($zone['countries']) ? 0 : 1;
                $filledTypes += empty($zone['zones']) ? 0 : 1;
                $filledTypes += empty($zone['provinces']) ? 0 : 1;

                return $filledTypes !== 1;
            })
            ->thenInvalid('Zone must have only one type of members ("countries", "zones", "provinces")')
        ;
    }

    /**
     * @param array $countriesProvinces
     */
    private function loadCountriesWithProvinces(array $countriesProvinces)
    {
        $countries = [];
        $countriesCodes = array_keys(Intl::getRegionBundle()->getCountryNames());

        foreach ($countriesCodes as $countryCode) {
            /** @var CountryInterface $country */
            $country = $this->countryFactory->createNew();
            $country->enable();
            $country->setCode($countryCode);

            $this->countryManager->persist($country);

            $countries[$countryCode] = $country;
        }

        foreach ($countriesProvinces as $countryCode => $provinces) {
            if (!isset($countries[$countryCode])) {
                continue;
            }

            $this->loadProvincesForCountry($provinces, $countries[$countryCode]);
        }
    }

    /**
     * @param array $zones
     */
    private function loadZones(array $zones)
    {
        foreach ($zones as $zoneCode => $zoneOptions) {
            $zoneName = $zoneOptions['name'];

            try {
                $zoneType = $this->getZoneType($zoneOptions);
                $zoneMembers = $this->getZoneMembers($zoneOptions);

                /** @var ZoneInterface $zone */
                $zone = $this->zoneFactory->createWithMembers($zoneMembers);
                $zone->setCode($zoneCode);
                $zone->setName($zoneName);
                $zone->setType($zoneType);

                $this->zoneManager->persist($zone);
            } catch (\InvalidArgumentException $exception) {
                throw new \InvalidArgumentException(sprintf(
                    'An exception was thrown during loading zone "%s" with code "%s"!',
                    $zoneName,
                    $zoneCode
                ), 0, $exception);
            }
        }
    }

    /**
     * @param array $provinces
     * @param CountryInterface $country
     */
    private function loadProvincesForCountry(array $provinces, CountryInterface $country)
    {
        /** @var GeoNameInterface $root */
        $root = $this->provinceFactory->createNew();
        $root->setName($country->getName());
        $root->setCode($country->getCode());

        $this->provinceManager->persist($root);

        foreach ($provinces as $provinceName => $child) {
            $province = $this->addGeoName($root, $provinceName, GeoNameInterface::TYPE_PROVINCE);
            $country->addProvince($province);

            if ($child) {
                foreach ($child as $districtName => $childern) {
                    $district = $this->addGeoName($province, $districtName, GeoNameInterface::TYPE_DISTRICT);

                    if ($childern) {
                        foreach ($childern as $subDistrictName) {
                            @list($subDistrictName, $postcode) = explode(',', $subDistrictName);
                            $subDistrict = $this->addGeoName($district, trim($subDistrictName), GeoNameInterface::TYPE_SUB_DISTRICT);
                            $subDistrict->setPostcode((int) trim($postcode));
                        }
                    }
                }
            }
        }
    }

    private function addGeoName(GeoNameInterface $root, $name, $type)
    {
        /** @var GeoNameInterface $geoName */
        $geoName = $this->provinceFactory->createNew();

        $geoName->setName($name);
        $geoName->setType($type);

        $root->addChild($geoName);

        $this->provinceManager->persist($geoName);

        return $geoName;
    }

    /**
     * @see ZoneInterface
     *
     * @param array $zoneOptions
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getZoneType(array $zoneOptions)
    {
        switch (true) {
            case count($zoneOptions['countries']) > 0:
                return ZoneInterface::TYPE_COUNTRY;
            case count($zoneOptions['provinces']) > 0:
                return ZoneInterface::TYPE_PROVINCE;
            case count($zoneOptions['zones']) > 0:
                return ZoneInterface::TYPE_ZONE;
            default:
                throw new \InvalidArgumentException('Cannot resolve zone type!');
        }
    }

    /**
     * @param array $zoneOptions
     *
     * @return array
     */
    private function getZoneMembers(array $zoneOptions)
    {
        $zoneType = $this->getZoneType($zoneOptions);

        switch ($zoneType) {
            case ZoneInterface::TYPE_COUNTRY:
                return $zoneOptions['countries'];
            case ZoneInterface::TYPE_PROVINCE:
                $provinces = [];

                foreach ($zoneOptions['provinces'] as $province) {
                    $province = $this->geoNameRepository->findOneBy(['name' => $province]);
                    $provinces[] = $province->getTranslatable()->getCode();
                }

                return $provinces;
            case ZoneInterface::TYPE_ZONE:
                return $zoneOptions['zones'];
            default:
                throw new \InvalidArgumentException('Cannot resolve zone members!');
        }
    }
}

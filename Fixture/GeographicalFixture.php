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
    private $geoFactory;

    /**
     * @var ObjectManager
     */
    private $geoManager;

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
    private $geoTranRepository;

    /**
     * @param FactoryInterface $countryFactory
     * @param ObjectManager $countryManager
     * @param FactoryInterface $geoFactory
     * @param ObjectManager $geoManager
     * @param ZoneFactoryInterface $zoneFactory
     * @param ObjectManager $zoneManager
     * @param RepositoryInterface $geoTranRepository
     */
    public function __construct(
        FactoryInterface $countryFactory,
        ObjectManager $countryManager,
        FactoryInterface $geoFactory,
        ObjectManager $geoManager,
        ZoneFactoryInterface $zoneFactory,
        ObjectManager $zoneManager,
        RepositoryInterface $geoTranRepository
    ) {
        $this->countryFactory = $countryFactory;
        $this->countryManager = $countryManager;
        $this->geoFactory = $geoFactory;
        $this->geoManager = $geoManager;
        $this->zoneFactory = $zoneFactory;
        $this->zoneManager = $zoneManager;
        $this->geoTranRepository = $geoTranRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $options): void
    {
        $this->loadCountriesWithProvinces((array) $options['provinces']);

        $this->countryManager->flush();
        $this->geoManager->flush();

        $this->loadZones($options['zones']);

        $this->zoneManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
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

            $this->loadProvincesForCountry((array) $provinces, $countries[$countryCode]);
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
        if (empty($provinces) && 'th' === strtolower($country->getCode())) {
            $this->countryManager->flush();
            $this->createThProvinces($country);
            return;
        }

        /** @var GeoNameInterface $root */
        $root = $this->geoFactory->createNew();
        $root->setName($country->getName());
        $root->setCode($country->getCode());

        $this->geoManager->persist($root);

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

    private function createThProvinces(CountryInterface $country)
    {
        echo "Downloading th geo data ...\r\n";
        $data = json_decode(file_get_contents('https://raw.githubusercontent.com/earthchie/jquery.Thailand.js/master/jquery.Thailand.js/database/raw_database/database.json'));
        echo "Download complete!\r\n";

        /** @var GeoNameInterface $root */
        $root = $this->geoFactory->createNew();
        $root->setName($country->getName());
        $root->setCode($country->getCode());

        $this->geoManager->persist($root);
        $this->geoManager->flush($root);

        $provinces = $districts = $subDistricts = [];

        foreach ($data as $i => $geo) {
            if (!$province = @$provinces[$geo->province]) {
                $province = $provinces[$geo->province] = $this->addGeoName($root, $geo->province, GeoNameInterface::TYPE_PROVINCE);
                $country->addProvince($province);
            }

            $districtSlug = sprintf('%s/%s', $geo->province, $geo->amphoe);

            if (!$district = @$districts[$districtSlug]) {
                $district = $districts[$districtSlug] = $this->addGeoName($province, $geo->amphoe, GeoNameInterface::TYPE_DISTRICT);
            }

            $subDistrictSlug = sprintf('%s/%s/%s/%s', $geo->province, $geo->amphoe, $geo->district, $geo->zipcode);

            if (!$subDistrict = @$subDistricts[$subDistrictSlug]) {
                $subDistrict = $subDistricts[$subDistrictSlug] = $this->addGeoName($district, $geo->district, GeoNameInterface::TYPE_SUB_DISTRICT);

                if ($geo->zipcode) {
                    $subDistrict->setPostcode($geo->zipcode);
                }
            }

            if (0 === $i % 100) {
                $this->geoManager->flush();
                echo number_format(($i / count($data)) * 100) . "% ";
            }
        }

        echo "Geo import done!\r\n";
    }

    private function addGeoName(GeoNameInterface $root, $name, $type)
    {
        /** @var GeoNameInterface $geoName */
        $geoName = $this->geoFactory->createNew();

        $geoName->setName($name);
        $geoName->setType($type);

        $root->addChild($geoName);

        $this->geoManager->persist($geoName);

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
                    if ($province = $this->geoTranRepository->findOneBy(['name' => $province])) {
                        $provinces[] = $province->getTranslatable()->getCode();
                    }
                }

                return $provinces;
            case ZoneInterface::TYPE_ZONE:
                return $zoneOptions['zones'];
            default:
                throw new \InvalidArgumentException('Cannot resolve zone members!');
        }
    }
}

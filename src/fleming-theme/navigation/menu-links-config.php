<?php

include_once 'link.php';

class MenuLinksConfig
{
    private const BASE = [
        'about' => [
            'title' => 'About Us',
            'target' => '/about-us/',

            'children' => [
                'about_amr' => [
                    'title' => 'About AMR',
                    'target' => '/about-us/about-amr/'
                ],
                'importance_of_data' => [
                    'title' => 'Importance of Data',
                    'target' => '/about-us/the-importance-of-data/'
                ],
                'investment' => [
                    'title' => 'Investment Areas',
                    'target' => '/investment-areas/',
                ],
                'partners' => [
                    'title' => 'Partners',
                    'target' => '/partners/',
                ],
                'advisory' => [
                    'title' => 'Technical Advisory Group',
                    'target' => '/technical-advisory-group/'
                ],
                'contact' => [
                    'title' => 'Contact Us',
                    'target' => '/contact-us/',
                ],
            ],
        ],
        'grants' => [
            'title' => 'Grants & Funding',
            'target' => '/grants-funding/',

            'children' => [
                'country-grant' => [
                    'title' => 'Country Grants',
                    'target' => '/grants-funding/country-grants/',
                ],
                'regional-grant' => [
                    'title' => 'Regional Grants',
                    'target' => '/grants-funding/regional-grants/',
                ],
                'fellowship' => [
                    'title' => 'Fellowships',
                    'target' => '/grants-funding/fellowships/',
                ],
                'other' => [
                    'title' => 'Global Projects',
                    'target' => '/grants-funding/global-projects/',
                ],
                'grants' => [
                    'title' => 'View All',
                    'target' => '/grants/',
                ],
                'apply' => [
                    'title' => 'How to Apply',
                    'target' => '/application-process/',
                ],
            ],
        ],
        'regions' => [
            'title' => 'Regions & Countries',
            'target' => '/regions-countries/',

            'children' => [
                // Ditto we don't have any of these at time of writing.
                // 'projects' => [
                //     'title' => 'Projects',
                //     'target' => '/projects/',
                // ],
            ],
        ],
        'knowledge' => [
            'title' => 'Knowledge & Resources',
            'target' => '/knowledge-resources/'
        ],
        'news' => [
            'title' => 'News & Events',
            'target' => '/news-events/',
        ],
    ];


    private static $all = null;
    private static $regions = null;

    public static function getAll()
    {
        if (self::$all === null) {
            self::initialiseAllMenuLinks();
        }
        return self::$all;
    }

    public static function getAllRegions()
    {
        if (self::$regions === null) {
            self::initialiseAllRegionLinks();
        }
        return self::$regions;
    }

    static function getFundCountryLinkConfigsWithinRegion($regionSlug)
    {
        return self::getCountriesWithinRegionWithRelationship($regionSlug, 'fund');
    }

    static function getPartnerCountryLinkConfigsWithinRegion($regionSlug)
    {
        return self::getCountriesWithinRegionWithRelationship($regionSlug, 'partner');
    }

    private static function getCountriesWithinRegionWithRelationship($regionSlug, $relationship)
    {

        $countryLinkConfigs = [];

        $countries = get_posts(array('post_type' => 'countries', 'numberposts' => -1));
        foreach ($countries as &$country) {
            $countryFields = get_field_objects($country->ID);

            if ($countryFields['relationship']['value'] !== $relationship
                || $countryFields['region']['value']->post_name !== $regionSlug) {
                continue;
            }

            $countryName = $country->post_title;
            $countrySlug = $country->post_name;
            $countryLinkTarget = get_permalink($country->ID);
            $countryLinkConfigs[$countrySlug] = [
                'title' => $countryName,
                'target' => $countryLinkTarget,
            ];
        }

        return $countryLinkConfigs;
    }

    public static function getUnderRoute(string ...$menuRouteKeys)
    {
        $config = self::getAll()[array_shift($menuRouteKeys)];
        while (count($menuRouteKeys) > 0) {
            $config = &$config['children'][array_shift($menuRouteKeys)];
            if (!isset($config)) {
                return null;
            }
        }
        return $config;
    }

    public static function configToLink($config)
    {
        $link = new Link();
        $link->setTitle($config['title']);
        $link->setTarget($config['target']);
        return $link;
    }

    public static function configsToLinks(array $configs)
    {
        return array_map('self::configToLink', $configs);
    }

    // Returns either an array of config path keys to the URL specified, or null if not found
    public static function findLink(string $url) {
        $configs = self::getAll();
        return self::findLinkRecurse($configs, $url);
    }

    private static function findLinkRecurse(array $configs, string $url) {
        foreach ($configs as $key => $value) {
            if ($value['target'] == $url) {
                return array($key);
            }
            if (isset($value['children'])) {
                $path = self::findLinkRecurse($value['children'], $url);
                if ($path) {
                    array_unshift($path, $key);
                    return $path;
                }
            }
        }
        return null;
    }

    private static function initialiseAllMenuLinks()
    {
        self::$all = self::BASE;
        self::populateMenuLinksWithRegions();
        self::populateMenuLinksWithFundCountries();
    }

    private static function initialiseAllRegionLinks()
    {
        $regions = get_posts(array('post_type' => 'regions', 'numberposts' => -1));

        $regionLinkConfigs = [];
        foreach ($regions as &$region) {
            $regionName = $region->post_title;
            $regionSlug = $region->post_name;
            $regionLinkTarget = get_permalink($region->ID);
            $regionLinkConfigs[$regionSlug] = [
                'title' => $regionName,
                'target' => $regionLinkTarget,
            ];
        }

        self::$regions = $regionLinkConfigs;
    }

    private static function populateMenuLinksWithRegions()
    {
        self::$all['regions']['children']
            = array_merge(self::getAllRegions(), self::$all['regions']['children']);
    }

    private static function populateMenuLinksWithFundCountries()
    {
        foreach (self::getAllRegions() as $regionSlug => $regionLinkConfig) {
            self::$all['regions']['children'][$regionSlug]['children']
                = self::getFundCountryLinkConfigsWithinRegion($regionSlug);

        }
    }
}

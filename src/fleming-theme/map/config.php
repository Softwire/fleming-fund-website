<?php
require_once __DIR__.'/../query-utilities.php';

function get_map_config(string $currentRegion = 'all') {
    $countries = get_posts(array('post_type' => 'countries', 'numberposts' => -1));

    add_current_region_to_map_config($mapConfig, $currentRegion);
    add_countries_and_regions_to_map_config($mapConfig, $countries);

    $mapConfig["countryIsClickable"] = true;

    return $mapConfig;
}

function get_country_map_config(string $countryID) {
    $country = get_post_data_and_fields($countryID);
    $regionSlug = $country['fields']['region']['value']->post_name;

    add_current_region_to_map_config($mapConfig, $regionSlug);
    add_country_to_map_config($mapConfig, $country);
    add_region_to_map_config($mapConfig, $regionSlug, [$country['fields']['country_code']['value']]);
    add_markers_to_map_config($mapConfig, $country['fields']['map_markers']['value']);

    return $mapConfig;
}

<?php

require_once __DIR__ . '/php/get-css-filename.php';
require_once 'navigation/index.php';
require_once 'map/config.php';

/**
 * NOTE:
 *
 * This is a CONTROLLER file.
 * It generates an object containing content for the page.
 *
 * You might also be interested in the VIEW.
 * VIEWs are located in the ./templates folder and have a .html file extension
 */

function get_nav_model() {
    return get_nav_builder()
        ->withMenuRoute('regions',  get_post_field( 'post_name'))
        ->build();
}

function fleming_get_content() {
    $fleming_content = array(
        "css_filename" => get_css_filename(),
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        "nav" => get_nav_model(),
        "map_config" => get_map_config(get_post_field( 'post_name')),
        "colour_scheme" => region_slug_to_colour_scheme_name(get_post_field( 'post_name'))
    );

    $this_region = get_current_post_data_and_fields();

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    if ($fleming_content['fields']['coordinator']['value']) {
        $fleming_content['fields']['coordinator']['value'] = person_with_post_data_and_fields(
                get_post_data_and_fields($fleming_content['fields']['coordinator']['value']->ID)
        );
    }
    if ($fleming_content['fields']['case_study']['value']) {
        $fleming_content['fields']['case_study']['value'] =
            get_post_data_and_fields($fleming_content['fields']['case_study']['value']->ID);
    }

    $fleming_content['fundCountryLinks'] =
        $fleming_content['nav']->getFundCountryLinksWithinRegion(get_post_field( 'post_name'));
    $fleming_content['partnerCountryLinks'] =
        $fleming_content['nav']->getPartnerCountryLinksWithinRegion(get_post_field( 'post_name'));

    $fleming_content["opportunities"] = array_map(
        'grant_with_post_data_and_fields',
        array_slice(
            get_referring_posts(get_the_ID(), 'grants', 'region'),
            0,
            2
        )
    );

    $fleming_content['rss_link_target'] = '/feed/region/?channel=' . $this_region['data']->post_name;

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

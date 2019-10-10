<?php

require_once __DIR__ . '/php/get-css-filename.php';
require_once 'query-utilities.php';
require_once 'navigation/index.php';

/**
 * NOTE:
 *
 * This is a CONTROLLER file.
 * It generates an object containing content for the page.
 *
 * You might also be interested in the VIEW.
 * VIEWs are located in the ./templates folder and have a .html file extension
 */


function fleming_get_content()
{
    $fleming_content = array(
        "title"  => get_raw_title(),
        "fields" => get_field_objects(),
        'nav'    => get_nav_builder()
            ->withMenuRoute('grants')
            ->withAdditionalBreadcrumb('View All')
            ->build(),
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $type_query = (isset($_GET["type"]) ? $_GET["type"] : null);
    $country_query = (isset($_GET["country"]) ? $_GET["country"] : null);
    $region_query = (isset($_GET["region"]) ? $_GET["region"] : null);

    $grant_type = get_grant_type_for_page(get_post()->post_parent);
    $activity_posts = get_activity_for_grant_type_and_post_type($grant_type, $type_query);

    if ($country_query) {
        $activity_posts = array_filter($activity_posts, function($grant) use ($country_query) {
            if (isset($grant['fields']['country_region'])) {
                $countries = $grant['fields']['country_region']['value']; // publications
            } elseif (isset($grant['fields']['country'])) {
                $countries = $grant['fields']['country']['value']; // events
            } else {
                return false;
            }

            return array_of_posts_contains_name($countries, $country_query);
        });
    }

    if ($region_query) {
        $activity_posts = array_filter($activity_posts, function($grant) use ($region_query) {
            if (isset($grant['fields']['country_region'])) {
                $regions = $grant['fields']['country_region']['value']; // publications
            } elseif (isset($grant['fields']['regions'])) {
                $regions = $grant['fields']['regions']['value']; // events
            } else {
                return false;
            }
            return array_of_posts_contains_name($regions, $region_query);
        });
    }

    process_list_query($fleming_content, 6, $activity_posts, $type_query, $country_query, $region_query, null);

    $fleming_content['types'] = array_map('map_post_to_filter_option', get_posts([
        'post_type'   => 'publication_types',
        'numberposts' => -1,
        'orderby'     => 'name',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]));
    $fleming_content['types'][] = [
        'query_string' => 'event',
        'display_string' => 'Events'
    ];

    return $fleming_content;
}

$template_name = 'page-activity';
if (isset($_GET['load_more'])) {
    $template_name = 'ajax-list-query-result';
}
include __DIR__ . '/use-templates.php';

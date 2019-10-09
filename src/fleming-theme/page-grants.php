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
    static $DEFAULT_NUMBER_OF_RESULTS = 4;

    $fleming_content = array(
        "title"  => get_raw_title(),
        "fields" => get_field_objects(),
        'nav'    => get_nav_builder()
            ->withMenuRoute('grants')
            ->withAdditionalBreadcrumb('View All')
            ->build(),
    );


    $type_query = (isset($_GET["type"]) ? $_GET["type"] : null);
    $country_query = (isset($_GET["country"]) ? $_GET["country"] : null);
    $region_query = (isset($_GET["region"]) ? $_GET["region"] : null);
    $status_query = (isset($_GET["status"]) ? $_GET["status"] : null);
    $max_number_of_results = isset($_GET["max-number-of-results"]) ? $_GET["max-number-of-results"] : $DEFAULT_NUMBER_OF_RESULTS;
    $load_more_counter = (isset($_GET["load_more"]) ? $_GET["load_more"] : 0); // This query parameter is set when making an ajax request for more results
    if ($load_more_counter) {
        $max_number_of_results = $max_number_of_results + $load_more_counter * $DEFAULT_NUMBER_OF_RESULTS;
    }

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $grant_type = get_page_by_path($type_query, 'OBJECT', 'grant_types');
    $grant_type_id = ($grant_type != null && $grant_type->post_status == 'publish') ? $grant_type->ID : null;
    $grants = get_full_grants($grant_type_id);
    if ($country_query) {
        $grants = array_filter($grants, function($grant) use ($country_query) {
            return array_of_posts_contains_name($grant['fields']['countries']['value'], $country_query);
        });
    }

    if ($region_query) {
        $grants = array_filter($grants, function($grant) use ($region_query) {
            return array_of_posts_contains_name($grant['fields']['region']['value'], $region_query);
        });
    }

    if ($status_query) {
        if ($status_query == 'open') {
            $grants = array_filter($grants, 'grant_is_open');
        }
        if ($status_query == 'active') {
            $grants = array_filter($grants, 'grant_is_active');
        }
    }

    $total_number_of_results = count($grants);
    $fleming_content['type_query'] = $type_query;
    $fleming_content['country_query'] = $country_query;
    $fleming_content['region_query'] = $region_query;
    $fleming_content['status_query'] = $status_query;
    $fleming_content['query_result']  = array_to_query_results($grants, $max_number_of_results);
    $fleming_content['max_number_of_results']  = $max_number_of_results;
    if ($max_number_of_results < $total_number_of_results) {
        $next_max_number_of_results = $max_number_of_results + $DEFAULT_NUMBER_OF_RESULTS;
        $fleming_content['load_more_url']  = "/grants/?type=$type_query&country=$country_query&region=$region_query&status=$status_query&max-number-of-results=$next_max_number_of_results";
    }
    $fleming_content['types'] = array_map('map_post_to_filter_option', get_posts([
        'post_type'   => 'grant_types',
        'numberposts' => -1,
        'orderby'     => 'name',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]));
    $fleming_content['countries'] = array_map('map_post_to_filter_option', get_posts([
        'post_type'          => 'countries',
        'numberposts'        => -1,
        'ignore_custom_sort' => true,
        'orderby'            => 'name',
        'order'              => 'ASC',
        'post_status'        => 'publish',
    ]));
    $fleming_content['regions'] = array_map('map_post_to_filter_option', get_posts([
        'post_type'          => 'regions',
        'numberposts'        => -1,
        'ignore_custom_sort' => true,
        'orderby'            => 'name',
        'order'              => 'ASC',
        'post_status'        => 'publish',
    ]));
    $fleming_content['statuses'] = [
        ['query_string' => 'open', 'display_string' => 'Applications Open'],
        ['query_string' => 'active', 'display_string' => 'Active']
    ];

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['load_more'])) {
    $template_name = 'ajax-' . $template_name;
}
include __DIR__ . '/use-templates.php';

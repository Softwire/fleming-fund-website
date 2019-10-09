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
    $grant_type = get_grant_type_for_page(get_post()->post_parent);


    $type_query = (isset($_GET["type"]) ? $_GET["type"] : null);
    $country_query = (isset($_GET["country"]) ? $_GET["country"] : null);
    $region_query = (isset($_GET["region"]) ? $_GET["region"] : null);
    $max_number_of_results = isset($_GET["max-number-of-results"]) ? $_GET["max-number-of-results"] : $DEFAULT_NUMBER_OF_RESULTS;

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $grants = get_activity_for_grant_type($grant_type);

    $total_number_of_results = count($grants);
    $fleming_content['type_query'] = $type_query;
    $fleming_content['country_query'] = $country_query;
    $fleming_content['region_query'] = $region_query;
    $fleming_content['query_result']  = array_to_query_results($grants, $max_number_of_results);
    $fleming_content['max_number_of_results']  = $max_number_of_results;
    if ($max_number_of_results < $total_number_of_results) {
        $next_max_number_of_results = $max_number_of_results + $DEFAULT_NUMBER_OF_RESULTS;
        $fleming_content['load_more_url']  = "/grants/?type=$type_query&country=$country_query&region=$region_query&max-number-of-results=$next_max_number_of_results";
    }
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

    return $fleming_content;
}

$template_name = 'page-activity';
if (isset($_GET['ajax'])) {
    $template_name = 'ajax-' . $template_name;
}
include __DIR__ . '/use-templates.php';

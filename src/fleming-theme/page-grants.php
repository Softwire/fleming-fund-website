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

    $type_query = (isset($_GET["type"]) ? $_GET["type"] : null);
    $country_query = (isset($_GET["country"]) ? $_GET["country"] : null);
    $region_query = (isset($_GET["region"]) ? $_GET["region"] : null);
    $status_query = (isset($_GET["status"]) ? $_GET["status"] : null);
    $max_number_of_results = isset($_GET["max-number-of-results"]) ? $_GET["max-number-of-results"] : 4;

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
            $grants = array_filter($grants, 'grant_deadline_is_in_future');
        }
        if ($status_query == 'closed') {
            $grants = array_filter($grants, function($grant) {
                return !grant_deadline_is_in_future($grant);
            });
        }
    }

    $total_number_of_results = count($grants);
     min($total_number_of_results, $max_number_of_results);
    $query_result = array_to_query_results(
        $grants,
        $max_number_of_results
    );
    $fleming_content['type_query'] = $type_query;
    $fleming_content['country_query'] = $country_query;
    $fleming_content['region_query'] = $region_query;
    $fleming_content['status_query'] = $status_query;
    $fleming_content['query_result']  = $query_result;
    $fleming_content['max_number_of_results']  = $max_number_of_results;
    if ($max_number_of_results < $total_number_of_results) {
        $next_max_number_of_results = $max_number_of_results + 4;
        $fleming_content['load_more_url']  = "/grants/?type=$type_query&country=$country_query&region=$region_query&status=$status_query&max-number-of-results=$next_max_number_of_results";
    }
    $fleming_content['grant_types'] = get_posts([
        'post_type'   => 'grant_types',
        'numberposts' => -1,
        'post_status' => 'publish',
    ]);
    $fleming_content['countries'] = get_posts([
        'post_type'          => 'countries',
        'numberposts'        => -1,
        'ignore_custom_sort' => true,
        'orderby'            => 'name',
        'order'              => 'ASC',
        'post_status'        => 'publish',
    ]);
    $fleming_content['regions'] = get_posts([
        'post_type'          => 'regions',
        'numberposts'        => -1,
        'ignore_custom_sort' => true,
        'orderby'            => 'name',
        'order'              => 'ASC',
        'post_status'        => 'publish',
    ]);
    $fleming_content['statuses'] = [
        ['query_string' => 'open', 'display_string' => 'Open'],
        ['query_string' => 'closed', 'display_string' => 'Closed']
    ];

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['ajax'])) {
    $template_name = 'ajax-' . $template_name;
}
include __DIR__ . '/use-templates.php';

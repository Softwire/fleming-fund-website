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
    $status_query = (isset($_GET["status"]) ? $_GET["status"] : null);

    $grant_type_id = null;
    if ($type_query) {
        $grant_type = get_page_by_path($type_query, 'OBJECT', 'grant_types');
        $grant_type_id = ($grant_type != null && $grant_type->post_status == 'publish') ? $grant_type->ID : null;
    }
    $grants_including_completed= get_full_grants($grant_type_id, true);
    $grants = array_filter($grants_including_completed, 'grant_is_current');
    $completed_grants = array_filter($grants_including_completed, 'grant_is_complete');

    if ($status_query) {
        if ($status_query == 'open') {
            $grants = array_filter($grants, 'grant_is_open');
        } elseif ($status_query == 'active') {
            $grants = array_filter($grants, 'grant_is_active');
        } elseif ($status_query == 'completed') {
            $grants = $completed_grants;
        }
    }

    if ($country_query) {
        $grants = array_filter($grants, function($grant) use ($country_query) {
            return isset($grant['fields']['countries']) && array_of_posts_contains_name($grant['fields']['countries']['value'], $country_query);
        });
    }

    if ($region_query) {
        $grants = array_filter($grants, function($grant) use ($region_query) {
            return isset($grant['fields']['region']) && array_of_posts_contains_name($grant['fields']['region']['value'], $region_query);
        });
    }

    $grants = sort_grants_by_type_then_status($grants);

    process_list_query($fleming_content, 4, $grants, $type_query, $country_query, $region_query, $status_query);

    $fleming_content['types'] = array_map('map_post_to_filter_option', get_posts([
        'post_type'   => 'grant_types',
        'numberposts' => -1,
        'orderby'     => 'name',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]));
    $fleming_content['statuses'] = [
        ['query_string' => 'open', 'display_string' => 'Applications Open'],
        ['query_string' => 'active', 'display_string' => 'Active'],
        ['query_string' => 'completed', 'display_string' => 'Completed']
    ];

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['load_more'])) {
    $template_name = 'ajax-list-query-result';
}
include __DIR__ . '/use-templates.php';

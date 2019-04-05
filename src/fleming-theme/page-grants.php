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
            ->withAdditionalBreadcrumb(get_raw_title())
            ->build(),
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $current_page = get_query_var('paged') ?: 1;

    $query_args = [
        'post_type' => 'grants',
        'paged'     => $current_page,
    ];
    $filter_string = isset($_GET["filter"]) ? $_GET["filter"] : (isset($_GET["type"]) ? $_GET["type"] : null);

    $query_result = null;

    if ($filter_string == "current") {
        $query_result = array_to_query_results(
            get_all_future_grants(),
            $current_page,
            10
        );
    } else {
        $grantType = get_page_by_path($filter_string, 'OBJECT', 'grant_types');
        if ($grantType != null && $grantType->post_status == 'publish') {
            $fleming_content['selected_grant_type'] = $grantType;
            $query_args["meta_query"]               = array(
                array(
                    'key'   => 'type',
                    'value' => $grantType->ID,
                ),
            );
        }

        if ($current_page == 1 && empty($fleming_content['selected_grant_type'])) {
            process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);
        }

        $query = new WP_Query($query_args);
        $query_result = get_query_results($query);

        foreach ($query_result['posts'] as &$grant) {
            $grant = grant_with_post_data_and_fields($grant);
        }
    }

    $fleming_content['filter_string'] = $filter_string;
    $fleming_content['query_result']  = $query_result;
    $fleming_content['grant_types']   = get_posts(array('post_type' => 'grant_types', 'numberposts' => -1));

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['ajax'])) {
    $template_name = 'ajax-' . $template_name;
}
include __DIR__ . '/use-templates.php';

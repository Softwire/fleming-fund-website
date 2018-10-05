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
        "title" => null,
        "fields" => get_field_objects(),
        "nav" => get_home_nav()
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $opportunities_cache_id = 'front_page_opportunities';

    $opportunities = get_transient($opportunities_cache_id);
    if (!is_array($opportunities)) {
        $opportunities = get_posts(array('post_type' => 'grants', 'numberposts' => -1));
        foreach ($opportunities as &$opportunity) {
            $opportunity = grant_with_post_data_and_fields(get_post_data_and_fields($opportunity->ID));
        }
        $opportunities = array_filter($opportunities, "grant_deadline_is_in_future");
        $opportunities = array_slice(sort_future_grants($opportunities), 0, 3);
        set_transient($opportunities_cache_id, $opportunities, min(MAX_CACHE_SECONDS, HOUR_IN_SECONDS / 2));
    }
    $fleming_content["opportunities"] = $opportunities;

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

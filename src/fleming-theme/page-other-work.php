<?php

require_once __DIR__ . '/php/get-css-filename.php';
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

function fleming_get_content() {
    $title = get_raw_title();
    $fleming_content = array(
        "title" => $title,
        "fields" => get_field_objects(),
        "colour_scheme" => "base",
        "nav" => get_nav_builder()
            ->withRouteFromPermalink($title)
            ->withDefaultRoute(array("grants"), $title)
            ->build()
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    add_supporting_content($fleming_content, get_other_grants_as_content());

    return $fleming_content;
}

function get_other_grants_as_content() {
    $cache_id = 'other_grants';
    $grants_content = get_transient($cache_id);
    if (!is_array($grants_content)) {
        $post_id = get_post()->ID;
        $other_grant_type = get_grant_type_for_page($post_id);
        $full_grants = get_full_grants($other_grant_type->ID);

        $sorted_grants = sort_past_grants($full_grants);

        $grants_content = get_grants_as_content($sorted_grants, 'Our Other Grants');
        set_transient($cache_id, $grants_content, min(MAX_CACHE_SECONDS, MINUTE_IN_SECONDS * 10));
    }
    return $grants_content;
}

$template_name = 'page';
include __DIR__ . '/use-templates.php';

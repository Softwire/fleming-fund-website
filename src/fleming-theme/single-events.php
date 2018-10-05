<?php

require_once __DIR__ . '/php/get-css-filename.php';
require_once 'navigation/index.php';
require_once 'query-utilities.php';

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
    $fleming_content = array(
        "css_filename" => get_css_filename(),
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        "nav" => get_nav_builder()
            ->withMenuRoute('news')
            ->withAdditionalBreadcrumb(get_raw_title())
            ->build(),
        "similar_events" => get_related_posts(
            get_current_post_data_and_fields(),
            2,
            true
        )
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    foreach ($fleming_content['similar_events'] as &$post) {
        $post = entity_with_post_data_and_fields($post);
    }

    if (!empty($fleming_content["fields"]["country"]["value"])) {
        foreach ($fleming_content["fields"]["country"]["value"] as &$location) {
            $location = $location->post_title;
        }
        $fleming_content["fields"]["country"]["value"] = implode(", ",
            $fleming_content["fields"]["country"]["value"]);
    }

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';
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

    return $fleming_content;
}

$template_name = 'page';
include __DIR__ . '/use-templates.php';

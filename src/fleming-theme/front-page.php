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
        "title"  => null,
        "fields" => get_field_objects(),
        "nav"    => get_home_nav(),
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $fleming_content["opportunities"] = array_slice(sort_future_grants(get_all_future_grants()), 0, 3);

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

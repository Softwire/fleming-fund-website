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

function fleming_get_content() {
    $fleming_content = array(
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        'nav' => get_nav_builder()->withMenuRoute('knowledge')->build()
    );

    $type = !empty($_GET["type"]) && $_GET["type"] !== 'event' ? get_page_by_path($_GET["type"], 'OBJECT', 'publication_types') :
        (!empty($_GET["type"]) ? $_GET["type"] : null);
    $country = !empty($_GET["country"]) ? get_page_by_path($_GET["country"], 'OBJECT', 'countries') : null;
    $page_number = !empty($_GET["page_number"]) ? $_GET["page_number"] : 1;

    $publications = get_knowledge_and_resources_publications_filtered_by_type_and_country($type, $country, $page_number, 10);

    if ($page_number == 1) {
        process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);
    }

    foreach($publications['posts'] as &$publication) {
        $publication = publication_with_post_data_and_fields($publication);
    }

    $fleming_content['query_result'] = $publications;

    $fleming_content["type_query"] = !empty($_GET["type"]) ? $_GET["type"] : null;
    $fleming_content["country_query"] = !empty($_GET["country"]) ? $_GET["country"] : null;

    $fleming_content['types'] = get_publication_types_for_type_filter();
    $fleming_content['countries'] = get_countries_for_country_filter();

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (!empty($_GET["page_number"])) {
    $template_name = 'ajax-list-query-result-items';
}
include __DIR__ . '/use-templates.php';

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
        'nav' => get_nav_builder()->withMenuRoute('grants')->build()
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $current_grants = get_current_grants_as_content();
    $case_study = get_case_study($fleming_content);

    add_supporting_content($fleming_content, $current_grants);
    if ($current_grants && $case_study) {
        add_supporting_content($fleming_content, ['acf_fc_layout' => 'horizontal_line', 'emphasis' => true]);
    }
    if ($case_study) {
        add_supporting_content($fleming_content, $case_study);
        add_supporting_content($fleming_content, get_link_button('/knowledge-resources/?type=case-study', 'See All Case Studies'));
    }

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

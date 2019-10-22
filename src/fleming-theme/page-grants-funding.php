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
    }

    return $fleming_content;
}

function get_current_grants_as_content() {
    $cache_id = 'current_grants';
    $grants_content = get_transient($cache_id);
    if (!is_array($grants_content)) {
        $current_grants = get_upcoming_or_else_most_recent_grants();
        $grants_content = get_grants_as_content($current_grants['grants'], $current_grants['heading']);
        set_transient($cache_id, $grants_content, min(MAX_CACHE_SECONDS, MINUTE_IN_SECONDS * 10));
    }
    return $grants_content;
}

function get_case_study($fleming_content) {
    if ($fleming_content['fields']['case_study'] && $fleming_content['fields']['case_study']['value']) {
        $case_study = get_post_data_and_fields($fleming_content['fields']['case_study']['value']->ID);
        return [
            'acf_fc_layout' => 'feature_case_study',
            'publication' => $case_study
        ];
    }
    return null;
}

$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

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
    if (isset($_GET["ajax"])) {
        return [
            "content_block" => get_latest_activity_as_content_block()
        ];
    }

    $fields = get_field_objects();

    $have_eligibility = $fields["criteria"]["value"]
        && ($fields["criteria"]["value"]["text_block_inner"] || $fields["criteria"]["value"]["criteria"]);
    $have_application_steps = $fields["application_steps"]["value"];

    $grant_type = get_type();

    $is_completed= ((isset($fields['status']) && $fields['status']['value'] == 6) || 
        (isset($fields['status_global_project']) && $fields['status_global_project']['value'] == 4) ||
        (isset($fields['status_fellowship']) && $fields['status_fellowship']['value'] == 6));

    $fleming_content = array(
        "css_filename" => get_css_filename(),
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        "nav" => get_nav_builder()
            ->withMenuRoute('grants', $grant_type->post_name)
            ->withAdditionalBreadcrumb(get_raw_title())
            ->build(),
        "have_eligibility" => $have_eligibility,
        "have_application_steps" => $have_application_steps,
        "grant_name" => $grant_type->post_name,
        "is_completed" => $is_completed
    );

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content'],
        $have_eligibility || $have_application_steps);

    if (($have_eligibility || $have_application_steps) && !$fleming_content["in_page_links"]) {
        $fleming_content["in_page_links"] = array();
    }
    if ($have_eligibility) {
        $fleming_content["in_page_links"][] = array("target" => "#eligibility", "title" => "Eligibility");
    }
    if ($have_application_steps) {
        $fleming_content["in_page_links"][] = array("target" => "#how-to-apply", "title" => "How to apply");
    }

    $thisGrant = grant_with_post_data_and_fields(get_current_post_data_and_fields());
    $fleming_content['colour_scheme'] = $thisGrant['colour_scheme'];

    if (!empty($fleming_content["fields"]["dates"]["value"])) {
        usort($fleming_content["fields"]["dates"]["value"], "compare_date_strings");
    }

    if (!empty($fleming_content["fields"]["dates"]["value"])) {
        $today["date"] = date('d/m/Y', time());
        $timeline_level = 255;
        for ($i = 0; $i < count($fleming_content["fields"]["dates"]["value"]); $i++) {
            $date = $fleming_content["fields"]["dates"]["value"][$i];
            if (compare_date_strings($today, $date) < 0) {$timeline_level = $i+1; break;};
        }
    }

    $fleming_content["timeline_level"] = $timeline_level;

    $fleming_content['grant_has_activity'] = $thisGrant['is_active'] && add_latest_activity_to_flexible_content($fleming_content, $grant_type);

    if (!$fleming_content['grant_has_activity']) {
        $related_posts = get_related_posts(
            get_current_post_data_and_fields(),
            2,
            true
        );
        $fleming_content['similar_proposals'] = array_map('entity_with_post_data_and_fields', $related_posts);
    }

    return $fleming_content;
}

function get_type() {
    return get_field_objects()['type']['value'];
}

function add_latest_activity_to_flexible_content(&$fleming_content, $grant_type) {
    if ($grant_type->post_name == 'fellowship') {
        return false;
    }

    foreach($fleming_content['fields']['flexible_content']['value'] as $key => $content_block) {
        if ($content_block['acf_fc_layout'] === 'single_grant_latest_activity') {
            $latest_activity = get_latest_activity_as_content_block();
            if ($latest_activity['links']) {
                $fleming_content['fields']['flexible_content']['value'][$key] = get_latest_activity_as_content_block();
                return true;
            }
        }
    }
    return false;
}

function get_latest_activity_as_content_block() {
    $number_of_results_per_batch = 3;
    $grant_id = get_post()->ID;
    $max_number_of_results = isset($_GET["max_number_of_results"]) ? $_GET["max_number_of_results"] : $number_of_results_per_batch;
    $ajax_load_more_results_counter = isset($_GET["load_more_activities"]) ? $_GET["load_more_activities"] : 0;
    $max_number_of_results = $max_number_of_results + $ajax_load_more_results_counter * $number_of_results_per_batch;
    $latest_activity_posts = get_activity_for_grant_type_and_post_type(null, null, $grant_id);

    $links = array_map(function ($activity) {
        return [
            'is_prominent' => false,
            'post' => $activity,
            'description_override' => null
        ];
    }, array_slice($latest_activity_posts, 0, $max_number_of_results));

    $next_max_number_of_results = $max_number_of_results + $number_of_results_per_batch;
    $load_more_url = count($latest_activity_posts) > $max_number_of_results ?
        "?max_number_of_results=$next_max_number_of_results" :
        null;

    return [
        'acf_fc_layout' => 'single_grant_latest_activity',
        'heading' => 'Latest Activity from ' . get_post()->post_title,
        'links' => $links,
        'max_per_row' => 'three-max',
        'load_more_url' => $load_more_url
    ];
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['ajax'])) {
    $template_name = 'ajax-single-grant-latest-activity';
}
include __DIR__ . '/use-templates.php';

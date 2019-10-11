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
    $fields = get_field_objects();

    $have_eligibility = $fields["criteria"]["value"]
        && ($fields["criteria"]["value"]["text_block_inner"] || $fields["criteria"]["value"]["criteria"]);
    $have_application_steps = $fields["application_steps"]["value"];

    $grant_type = get_type();

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
        "similar_proposals" => get_related_posts(
            get_current_post_data_and_fields(),
            2,
            true
        )
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

    foreach ($fleming_content['similar_proposals'] as &$post) {
        $post = entity_with_post_data_and_fields($post);
    }

    if ($grant_type->post_name == 'country-grant' || $grant_type->post_name == 'regional-grant') {
        add_latest_activity_supporting_content($fleming_content, $grant_type);
    }
    return $fleming_content;
}

function get_type() {
    return get_field_objects()['type']['value'];
}

function add_latest_activity_supporting_content(&$fleming_content, $grant_type) {
    $post_id = get_post()->ID;
    $latest_activity_posts = get_posts([
        'post_type' => ['events', 'publications'],
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'numberposts' => 3,
        'meta_query' => [
            'relation' => 'OR',
            array(
                'key' => 'grants',
                'value' => $post_id,
                'compare' => '='
            ),
            array(
                'key' => 'grants',
                'value' => serialize(strval($post_id)),
                'compare' => 'LIKE'
            )
        ]
    ]);

    if (!is_array($latest_activity_posts) || count($latest_activity_posts) == 0) {
        return;
    }
    $latest_activity_with_data = array_map(function ($post) {
        return get_post_data_and_fields($post->ID);
    }, $latest_activity_posts);

    $links = array_map(function ($activity) {
        return [
            'is_prominent' => false,
            'post' => publication_with_post_data_and_fields($activity),
            'description_override' => null
        ];
    }, $latest_activity_with_data);
    $recent_activity_content = [
        'acf_fc_layout' => 'links_to_other_posts',
        'heading' => 'Latest Activity from ' . get_post()->post_title,
        'links' => $links,
        'max_per_row' => 'three-max'
    ];
    add_supporting_content($fleming_content, $recent_activity_content);
    add_supporting_content($fleming_content, get_link_button(get_field_objects($grant_type->ID)['overview_page']['value'] . 'activity', 'View all'));
}

$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

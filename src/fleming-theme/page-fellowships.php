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

    get_case_studies_supporting_content($fleming_content);

    return $fleming_content;
}

function get_case_studies_supporting_content(&$fleming_content) {
    $cache_id = 'fellowship_case_studies';
    $post_id = get_post()->ID;
    $fellowship_grant_type = get_grant_type_for_page($post_id);

    $content = get_transient($cache_id);
    if (!is_array($content)) {
        // No cached data:
        $case_study_types = get_posts([
            'post_type' => 'publication_types',
            'post_status' => 'publish',
            'title' => 'Case Study'
        ]);
        if (count($case_study_types) == 0) {
            return;
        }

        $case_studies_query_args = [
            'post_type'  => ['publications'],
            'orderby' => 'publication_date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'type',
                    'value' => $case_study_types[0]->ID,
                    'compare' => '='
                )
            )
        ];
        $query_result = get_query_results(new WP_Query($case_studies_query_args));
        $fellowship_case_studies = filter_publications_or_events_by_grant_type($query_result['posts'], $fellowship_grant_type);
        $recent_fellowship_case_studies = array_slice($fellowship_case_studies, 0 , 3);
        $links = array_map(function($recent_activity) {
            return [
                'is_prominent' => false,
                'post' => publication_with_post_data_and_fields($recent_activity),
                'description_override' => null
            ];
        }, $recent_fellowship_case_studies);
        $content = [
            'acf_fc_layout' => 'links_to_other_posts',
            'heading' => 'Fellowship Case Studies',
            'links' => $links,
            'max_per_row' => 'three-max'
        ];
        set_transient($cache_id, $content, min(MAX_CACHE_SECONDS, MINUTE_IN_SECONDS * 10));
    }

    if ($content && count($content['links']) > 0) {
        add_supporting_content($fleming_content, $content);
        add_supporting_content($fleming_content, get_link_button("/knowledge-resources/?type=case-study", 'View all'));
    }
}

$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

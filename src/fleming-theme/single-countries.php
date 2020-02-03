<?php

require_once __DIR__ . '/php/get-css-filename.php';
require_once 'navigation/index.php';
require_once 'query-utilities.php';
require_once 'functions.php';

/**
 * NOTE:
 *
 * This is a CONTROLLER file.
 * It generates an object containing content for the page.
 *
 * You might also be interested in the VIEW.
 * VIEWs are located in the ./templates folder and have a .html file extension
 */

function get_nav_model()
{
    $regionSlug = get_field_objects()['region']['value']->post_name ?? '';
    return get_nav_builder()
        ->withMenuRoute('regions', $regionSlug)
        ->withAdditionalBreadcrumb(get_raw_title())
        ->build();
}

function fleming_get_content()
{
    $fleming_content = array(
        "css_filename" => get_css_filename(),
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        "nav" => get_nav_model(),
    );

    $this_country = get_current_post_data_and_fields();

    $country_slug = $this_country['data']->post_name;

    process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);

    $fleming_content['colour_scheme'] = region_slug_to_colour_scheme_name($fleming_content["fields"]["region"]["value"]->post_name);

    if ($fleming_content["fields"]["case_study"]["value"]) {
        $fleming_content["fields"]["case_study"]["value"] = get_post_data_and_fields($fleming_content["fields"]["case_study"]["value"]->ID);
    }

    $region_data = get_post_data_and_fields($fleming_content["fields"]["region"]["value"]->ID);
    if ($region_data["fields"]["coordinator"]["value"]) {
        $fleming_content["coordinator"] = get_post_data_and_fields($region_data["fields"]["coordinator"]["value"]->ID);
    }

    // qq - include region and worldwide grants?
    // qq - sort? filter?
    $fleming_content["opportunities"] = array_map(
        'grant_with_post_data_and_fields',
        array_slice(
            get_referring_posts(get_the_ID(), 'grants', 'countries'),
            0,
            2
        )
    );

    $allProjects = [];
    foreach ($fleming_content["opportunities"] as $grant) {
        $projectsForGrant = array_map(
            'project_with_post_data_and_fields',
            get_referring_posts($grant['data']->ID, 'projects', 'grant')
        );
        $allProjects = array_merge($allProjects, $projectsForGrant);
    }
    // qq - sort? filter?
    $fleming_content["projects"] = array_slice($allProjects, 0, 2);

    $fleming_content['rss_link_target'] = '/feed/country/?channel=' . $country_slug;

    //query for the events and publications of the country
    $latest_activity_query_result = query_news_events($fleming_content, $country_slug, false);

    //Select one or two posts to display
    $activities = $latest_activity_query_result['posts'];

    if (count($activities) > 1) {
        if ($activities[0]['should_display_prominently']) {
            $activities = [$activities[0]];
        }
        else if ($activities[1]['should_display_prominently']) {
            $activities = [$activities[1]];
        }
        else {
            $activities = [$activities[0], $activities[1]];
        }
    }

    $fleming_content["latest_activity"] = $activities;

    $fleming_content["view_all_activity_button"] = get_link_button("/news-events/?country=".$country_slug, "View All", "turquoise");

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

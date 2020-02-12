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
    $fleming_content = [
        "css_filename" => get_css_filename(),
        "title" => get_raw_title(),
        "fields" => get_field_objects(),
        "nav" => get_nav_model()
    ];

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

    $grants_in_this_country = array_map('grant_with_post_data_and_fields', get_referring_posts(get_the_ID(), 'grants', 'countries'));
    $fleming_content["grants_in_this_country"] = array_slice($grants_in_this_country, 0, 2);
    $fleming_content["open_grants_in_this_country"] =  array_slice(array_filter($grants_in_this_country, 'grant_is_open'), 0, 2);

    $fleming_content['rss_link_target'] = '/feed/country/?channel=' . $country_slug;

    //query for the events and publications of the country
    $latest_activity = get_news_and_events(1, 2, $country_slug);
    $fleming_content["latest_activity"] = $latest_activity['posts'];

    $fleming_content["view_all_button"] = [
        "grants_in_this_country" =>  get_link_button("/grants/?&country=".$country_slug, "View all", "turquoise"),
        "open_grants_in_this_country" => get_link_button("/grants/?&country=".$country_slug."&status=open", "View all", "turquoise"),
        "latest_activity" => get_link_button("/news-events/?country=".$country_slug, "View all", "turquoise")
    ];

    return $fleming_content;
}


$template_name = pathinfo(__FILE__)['filename'];
include __DIR__ . '/use-templates.php';

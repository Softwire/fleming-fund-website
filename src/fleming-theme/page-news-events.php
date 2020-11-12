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

function fleming_get_content()
{
    $fleming_content = array(
        "title"  => get_raw_title(),
        "fields" => get_field_objects(),
        "nav"    => get_nav_builder()->withMenuRoute('news')->build(),
    );

    $type = !empty($_GET["type"]) && $_GET["type"] !== 'event' ? get_page_by_path($_GET["type"], 'OBJECT', 'publication_types') :
        (!empty($_GET["type"]) ? $_GET["type"] : null);
    $country = !empty($_GET["country"]) ? get_page_by_path($_GET["country"], 'OBJECT', 'countries') : null;
    $page_number = !empty($_GET["page_number"]) ? $_GET["page_number"] : 1;

    $news_and_events = get_news_and_events_filtered_by_type_and_country($type, $country, $page_number, 10);    
    
    if ($page_number == 1) {
        process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);
    }

    foreach ($news_and_events['posts'] as &$post) {
        if ($post['data']->post_type === 'publications') {
            $post = publication_with_post_data_and_fields($post);
        }
        $post['should_display_prominently'] = should_display_prominently($post);
    }
    
    /*
     * Re-order the posts so that they are arranged nicely in two columns even if some of them will be 'prominent' and take an entire row.
     * Assumes two rows. This isn't always required, but we always re-order for consistency.
     * Instead of:
     * <POST1>
     * <PROMINENT_POST2>
     * <POST3>   <POST4>
     *
     * We get:
     * <PROMINENT_POST2>
     * <POST1>   <POST3>
     * <POST4>
     */
    $ordered_posts  = [];
    $held_back_post = null;
    $length         = count($news_and_events['posts']);
    unset($post);
    for ($i = 0; $i < $length; $i++) {
        $post =  $news_and_events['posts'][$i];
        if ($post['should_display_prominently']) {
            $ordered_posts[] = $post;
        } else {
            if ($held_back_post) {
                $ordered_posts[] = $held_back_post;
                $ordered_posts[] = $post;
                $held_back_post  = null;
            } else {
                $held_back_post = $post;
            }
        }
    }
    if ($held_back_post) {
        $ordered_posts[] = $held_back_post;
    }

    $news_and_events['posts'] = $ordered_posts;

    $fleming_content['query_result'] = $news_and_events;
    $fleming_content["type_query"] = !empty($_GET["type"]) ? $_GET["type"] : null;
    $fleming_content["country_query"] = !empty($_GET["country"]) ? $_GET["country"] : null;

    $fleming_content['types'] = get_publication_types_and_event_for_type_filter();
    $fleming_content['countries'] = get_countries_for_country_filter();

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (!empty($_GET["page_number"])) {
    $template_name = 'ajax-list-query-result-items';
}
include __DIR__ . '/use-templates.php';

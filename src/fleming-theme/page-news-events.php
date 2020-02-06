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

    $country_slug = $_GET["country"] ?? null;
    $current_page = get_query_var('paged') ?: 1;

    if (isset($country_slug)) {
        $news_and_events = get_news_and_events($current_page, 10, $country_slug);
        $country = get_page_by_path($country_slug, 'OBJECT', 'countries');
        $fleming_content['selected_country'] = $country;
    }
    else {
        $news_and_events = get_news_and_events($current_page, 10);
    }

    if ($current_page == 1 && empty($fleming_content['selected_country'])) {
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
    $fleming_content['countries']    = get_posts(array(
        'post_type'          => 'countries',
        'numberposts'        => -1,
        'ignore_custom_sort' => true,
        'orderby'            => 'name',
        'order'              => 'ASC',
    ));

    return $fleming_content;
}

$template_name = pathinfo(__FILE__)['filename'];
if (isset($_GET['ajax'])) {
    $template_name = 'ajax-' . $template_name;
}
include __DIR__ . '/use-templates.php';

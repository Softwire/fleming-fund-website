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

    $query_result = query_news_events($fleming_content, $_GET["country"] ?? null);
    
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
    $length         = count($query_result['posts']);
    unset($post);
    for ($i = 0; $i < $length; $i++) {
        $post = $query_result['posts'][$i];
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

    $query_result['posts'] = $ordered_posts;

    $fleming_content['query_result'] = $query_result;
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

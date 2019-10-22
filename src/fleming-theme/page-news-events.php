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

function should_display_prominently($post)
{
    return $post['data']->post_type === 'publications'
        && isset($post['fields']['display_card_prominently'])
        && $post['fields']['display_card_prominently']['value']
        && $post['can_display_prominently'];
}

function fleming_get_content()
{
    $fleming_content = array(
        "title"  => get_raw_title(),
        "fields" => get_field_objects(),
        "nav"    => get_nav_builder()->withMenuRoute('news')->build(),
    );

    $current_page = get_query_var('paged') ?: 1;

    $query_args = get_news_events_query_args();
    $query_args['paged'] = $current_page;
    $country = null;
    if (isset($_GET["country"])) {
        $country = get_page_by_path($_GET["country"], 'OBJECT', 'countries');
        if ($country != null && $country->post_status == 'publish') {
            $fleming_content['selected_country'] = $country;
            $query_args["meta_query"]            = array(
                'relation' => 'and',
                $query_args["meta_query"],
                array(
                    'relation' => 'or',
                    array(
                        'key'   => 'country',
                        'value' => $country->ID,
                    ),
                    array(
                        'key'     => 'country_region',
                        'value'   => serialize(strval($country->ID)),
                        'compare' => 'LIKE',
                    ),
                ),
            );
        }
    }

    if ($current_page == 1 && empty($fleming_content['selected_country'])) {
        process_flexible_content($fleming_content, $fleming_content['fields']['flexible_content']);
    }

    $query        = new WP_Query($query_args);
    $query_result = get_query_results($query);

    foreach ($query_result['posts'] as &$post) {
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

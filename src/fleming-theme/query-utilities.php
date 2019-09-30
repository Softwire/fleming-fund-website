<?php

function get_post_data_and_fields($postID) {
    if (!isset($postID)) {
        return null;
    }
    $result = [
        'data'=>get_post($postID),
        'permalink'=>get_permalink($postID),
        'fields'=>get_field_objects($postID)
    ];
    if ($result['data']) {
        $result['data']->page_title = get_raw_title($postID);
        $result['data']->guid = htmlspecialchars_decode($result['data']->guid);
    }
    return $result;
}

// As above except we want to fetch the current post from the loop, i.e. we want no post ID.
// Commonise but keep the null check for the previous function somehow?
function get_current_post_data_and_fields() {
    $result = [
        'data'=>get_post(),
        'permalink'=>get_permalink(),
        'fields'=>get_field_objects()
    ];
    $result['data']->page_title = get_raw_title();
    $result['data']->guid = htmlspecialchars_decode($result['data']->guid);
    return $result;
}

// Use this function to query posts that refer back to a given post ID where we're sure that the
// referring posts' field containing this ID is single-valued only, i.e. we can test by equality.
// If it is multi-valued it will be stored as a serialized PHP array and you will need to use the
// more complex get_referring_posts below.
function get_referring_posts_single_valued($postID, $post_type, $reference_type) {
    $posts = get_posts(
        array('post_type'=>$post_type, 'numberposts'=>-1, 'meta_query'=>array(
                array(
                    'key'=>$reference_type,
                    'value'=>$postID,
                    'compare'=>'IN'
                )
            )
        )
    );
    foreach($posts as &$post) {
        $post = get_post_data_and_fields($post->ID);
    }
    foreach($posts as &$post) {
        unset($post);
    }
    return array_values($posts); // reset array indices to 0, 1, 2, ...
}

// This looks for posts that refer back to a given post ID, supporting multi-valued ID fields that are
// stored in PHP's serialization format.
// This may involve reading two much data then filtering down, so results are cached for five minutes.
function get_referring_posts($postID, $post_type, $reference_type) {
    $dependent_arguments = [ $post_type, $reference_type, $postID ];
    $cache_id = 'referring_posts_' . implode('_', $dependent_arguments);

    $referring_posts = get_transient($cache_id);

    if (false === $referring_posts)
    {
        // Start with a 'LIKE' meta query. This will return any
        // N.B. this might not match any value we're searching in an array-type field where the value contains a quote.
        // However we're almost always searching for integers so this should be fine.
        $posts = get_posts(
            array('post_type' => $post_type, 'numberposts' => -1, 'meta_query' => array(
                array(
                    'key' => $reference_type,
                    'value' => $postID,
                    'compare' => 'LIKE'
                )
            ))
        );

        // Load all fields
        foreach ($posts as &$post) {
            $post = get_post_data_and_fields($post->ID);
        }
        unset($post);

        // Filter down to the correct subset we want by checking exact matches in the fields this time.
        $posts = array_filter($posts, function($post) use($postID, $reference_type) {
            if (is_array($post['fields'][$reference_type]['value'])) {
                foreach($post['fields'][$reference_type]['value'] as $reference) {
                    if ($reference->ID == $postID) {
                        return true;
                    }
                }
            } else if (is_object($post['fields'][$reference_type]['value'])) {
                return $post['fields'][$reference_type]['value']->ID == $postID;
            }
            return false;
        });

        $referring_posts = array_values($posts); // reset array indices to 0, 1, 2, ...
        set_transient($cache_id, $referring_posts, min(MAX_CACHE_SECONDS, 60 * 5));
    }

    return $referring_posts;
}

function get_query_results($query = NULL) {
    if ($query == null) {
        global $wp_query;
        $query = $wp_query;
    }
    global $paged, $page_size;
    $max_page = $query->max_num_pages;
    $page_number = $paged ? $paged : 1;

    $posts = [];
    for ($i = 0; $i < 10 && $query->have_posts(); $i++) {
        $query->the_post();
        $posts[] = get_current_post_data_and_fields();
    }
    wp_reset_postdata();

    return array(
        "posts" => $posts,
        "query" => get_search_query(),
        "max_page" => $max_page,
        "pagination_links" => paginate_links([
            'show_all' => true,
            'prev_next' => true,
            'total' => $max_page,
            'current' => $page_number
        ]),
        "summary" => query_summary($query->found_posts, $page_size, $page_number, $max_page)
    );
}

function query_summary($total, $page_size, $page_number, $max_page) {
    $total_results_summary = strval($total) . " result" . ($total == 1 ? "" : "s");
    $pagination_summary = $max_page > 1 ? "" . strval(($page_number - 1)*$page_size + 1) . "-" . strval(min($page_number*$page_size, $total)) . " of " : "";
    return $pagination_summary . $total_results_summary;
}

function get_related_posts($post, $limit=2, $same_post_type_only) {
    $dependent_arguments = [ // the (string-ified) arguments on which the results may depend
        $post['data']->ID,
        $limit,
        (int) $same_post_type_only
    ];
    $cache_id = 'relevanssi_similar_' . implode('_', $dependent_arguments);
    $related_posts = get_transient($cache_id);
    if (empty($related_posts)) {
        $query_args = [
            's' => $post['data']->post_title,
            'posts_per_page' => 1, // irrelevant but necessary
            'operator' => 'or'
        ];
        $query = new WP_Query($query_args);
        relevanssi_do_query($query);
        $related_posts = [];
        foreach ($query->posts as $r_post) {
            if ($r_post->ID == $post['data']->ID) continue;
            if ($same_post_type_only && $r_post->post_type != $post['data']->post_type) continue;
            $related_posts[] = get_post_data_and_fields($r_post->ID);
            if (count($related_posts) >= $limit) break;
        }
        set_transient($cache_id, $related_posts, min(MAX_CACHE_SECONDS, HOUR_IN_SECONDS));
    }
    return $related_posts;
}

function get_type() {
    return get_field_objects()['type']['value']->post_name;
}

function compare_date_strings($string1, $string2) {
    $date1 = explode("/", $string1["date"]);
    $date2 = explode("/", $string2["date"]);
    if ($date2[2] != $date1[2]) return (int) $date1[2] - (int) $date2[2];
    if ($date2[1] != $date1[1]) return (int) $date1[1] - (int) $date2[1];
    if ($date2[0] != $date1[0]) return (int) $date1[0] - (int) $date2[0];
    return 0;
}

function get_all_future_grants() {
    $future_grants_cache_id = 'all_future_grants';
    $future_grants = get_transient($future_grants_cache_id);

    if (!is_array($future_grants)) {
        $grants = get_full_grants(null);
        $future_grants = array_filter($grants, "grant_deadline_is_in_future");
        set_transient($future_grants_cache_id, $future_grants, min(MAX_CACHE_SECONDS, MINUTE_IN_SECONDS * 10));
    }
    return $future_grants;
}

function array_to_query_results($posts, $max_number_of_results) {
    $total = count($posts);
    $summary = strval($total);
    if ($total == 1) {
        $summary .= ' result';
    } else {
        $summary .= ' results';
    }

    return array(
        "posts" => array_slice($posts, 0, $max_number_of_results),
        "summary" => $summary,
    );
}

function get_case_study($fleming_content) {
    if ($fleming_content['fields']['case_study'] && $fleming_content['fields']['case_study']['value']) {
        $case_study = get_post_data_and_fields($fleming_content['fields']['case_study']['value']->ID);
        return [
            'acf_fc_layout' => 'publication_with_image_block',
            'heading' => 'Feature Case Study',
            'publication' => $case_study
        ];
    }
    return null;
}

function get_current_grants_as_content() {
    $cache_id = 'current_grants';
    $current_grants = get_transient($cache_id);
    if (!is_array($current_grants)) {
        $current_grants = get_upcoming_or_else_most_recent_grants(null);
        set_transient($cache_id, $current_grants, min(MAX_CACHE_SECONDS, HOUR_IN_SECONDS / 2));
    }
    return get_grants_as_content($current_grants);
}

function get_grant_type_for_page($post_id) {
    $grant_types_query_args = [
        'post_type' => 'grant_types',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'overview_page',
                'value' => $post_id,
                'compare' => '='
            )
        )
    ];
    $grant_types = get_posts($grant_types_query_args);

    if ($grant_types && sizeof($grant_types) >= 1 && isset($grant_types[0])) {
        return $grant_types[0];
    }
    return null;
}

// For a grant type page (Country Grants, Regional Grants) show counts for grants of this type.
function show_grants_for_page(&$fleming_content) {
    $post_id = get_post()->ID;
    if (!$post_id) {
        return;
    }

    $cache_id = 'grants_by_type_' . $post_id;
    $grant_numbers = get_transient($cache_id);

    if (!is_array($grant_numbers)) {
        // No cached data.
        $grant_type = get_grant_type_for_page($post_id);
        if ($grant_type) {
            // Look up all grants of this type. We only want two but we can't currently order this
            // in the query :-( so we'll read them all in and filter / sort in code
            $all_grants = get_full_grants($grant_type->ID);
            $open_grants = array_filter($all_grants, "grant_is_open");
            $active_grants = array_filter($all_grants, "grant_is_active");
            $grant_numbers = [
                'grant_type' => $grant_type->post_name,
                'total_number_of_grants' => count($all_grants),
                'number_of_open_grants' => count($open_grants),
                'number_of_active_grants' => count($active_grants),
            ];
        } else {
            // We couldn't read the grant type. Cache something as a failure.
            $grant_numbers = [
                'grant_type' => 'error'
            ];
        }
        set_transient($cache_id, $grant_numbers, min(MAX_CACHE_SECONDS, HOUR_IN_SECONDS / 2));
    }

    $fleming_content['total_number_of_grants'] = $grant_numbers['total_number_of_grants'];
    $fleming_content['number_of_open_grants'] = $grant_numbers['number_of_open_grants'];
    $fleming_content['number_of_active_grants'] = $grant_numbers['number_of_active_grants'];
}

// For a grant type page (Country Grants, Regional Grants) show some recent activity for this type.
function show_activity_for_page(&$fleming_content) {
    $post_id = get_post()->ID;
    if (!$post_id) {
        return;
    }
    $cache_id = 'recent_activity_' . $post_id;
    $page_grant_type = get_grant_type_for_page($post_id);
    $grant_type_name = $page_grant_type->post_name;

    $recent_activity_content = get_transient($cache_id);
    if (!is_array($recent_activity_content)) {
        // No cached data:
        $query_args = [
            'post_type'  => ['events', 'publications'],
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        $query_result = get_query_results(new WP_Query($query_args));
        $filtered_activities = array_filter($query_result['posts'], function($activity) use ($page_grant_type) {
            if (!isset($activity['fields']['grants'])) {
                return false;
            }
            $activity_grants = $activity['fields']['grants']['value'];
            foreach($activity_grants as $grant) {
                $activity_grant_type = get_field_objects($grant->ID)['type']['value'];
                if ($activity_grant_type && $activity_grant_type->ID == $page_grant_type->ID) {
                    return true;
                }
            }
            return false;
        });
        $recent_activities = array_slice($filtered_activities, 0 , 3);
        $links = array_map(function($recent_activity) {
            return [
                'is_prominent' => false,
                'post' => publication_with_post_data_and_fields($recent_activity),
                'description_override' => null
            ];
        }, $recent_activities);
        $recent_activity_content = [
            'acf_fc_layout' => 'links_to_other_posts',
            'heading' => 'Latest Activity from ' . get_post()->post_title,
            'links' => $links,
            'max_per_row' => 'three-max'
        ];
        set_transient($cache_id, $recent_activity_content, min(MAX_CACHE_SECONDS, HOUR_IN_SECONDS / 2));
    }

    if ($recent_activity_content && count($recent_activity_content['links']) > 0) {
        add_supporting_content($fleming_content, $recent_activity_content);
        add_supporting_content($fleming_content, get_link_button("/activity/?grant_type=$grant_type_name", 'View all'));
    }
}

function get_full_grants($grant_type_id) {
    $query_args = [
        'post_type' => 'grants',
        'numberposts' => -1
    ];
    if ($grant_type_id) {
        $query_args['meta_query'] = [[
            'key' => 'type',
            'value' => $grant_type_id,
            'compare' => '='
        ]];
    }
    $grants = get_posts($query_args);

    // Read extra fields and process dates etc.
    $full_grants = array();
    foreach ($grants as $grant) {
        $full_grants[] = grant_with_post_data_and_fields(get_post_data_and_fields($grant->ID));
    }

    return $full_grants;
}

function get_upcoming_or_else_most_recent_grants($grant_type) {
    $full_grants = get_full_grants($grant_type ? $grant_type->ID : null);
    $showing_future_grants = false;

    // Find any future grants
    $future_grants = array_filter($full_grants, "grant_deadline_is_in_future");
    if ($future_grants && sizeof($future_grants) >= 1) {
        // We have future grants. Sort them earliest deadline first.
        sort_future_grants($future_grants);
        $full_grants = $future_grants;
        $showing_future_grants = true;
    } else {
        // We don't have future grants. Show the most recent previous grants instead.
        sort_past_grants($full_grants);
    }

    return [
        'grant_type' => $grant_type ? $grant_type->post_name : null,
        'grants' => array_slice($full_grants, 0, 2),
        'is_future' => $showing_future_grants
    ];
}

function get_link_button($url, $text = 'View More') {
    return [
        'acf_fc_layout' => 'link_button',
        'link' => [
            'title' => null,
            'url' => $url,
            'target' => null
        ],
        'button_text' => $text,
        'centred' => true
    ];
}

function array_of_posts_contains_name($array_of_posts, $target_name) {
    if (!$array_of_posts) {
        return false;
    }
    $names = array_map(function($country_post) {
        return $country_post->post_name;
    }, $array_of_posts);

    return in_array($target_name, $names);
}

function get_grants_as_content($grants) {
    if ($grants && is_array($grants['grants']) && sizeof($grants['grants']) > 0) {
        $showing_future_grants = $grants['is_future'];

        // Build a links-to-other-posts section
        $links = array();
        foreach ($grants['grants'] as $grant) {
            $links[] = [
                'is_prominent' => false,
                'post' => $grant,
                'description_override' => null
            ];
        }
        return [
            'acf_fc_layout' => 'links_to_other_posts',
            'heading' => $showing_future_grants ? 'Current and upcoming opportunities' : 'Recent opportunities',
            'links' => $links
        ];
    }
}

function add_supporting_content(&$fleming_content, $supporting_content) {
    if (!isset($fleming_content['fields']['supporting_content'])) {
        $fleming_content['fields']['supporting_content'] = array();
    }
    if (!isset($fleming_content['fields']['supporting_content']['value'])) {
        $fleming_content['fields']['supporting_content']['value'] = array();
    }
    $fleming_content['fields']['supporting_content']['value'][] = $supporting_content;
}


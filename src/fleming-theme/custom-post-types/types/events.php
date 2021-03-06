<?php

register_post_type('events', array(
    'labels'                => array(
        'name' => __( 'Events' ),
        'singular_name' => __( 'Event' )
    ),
    'description'           => '',
    'exclude_from_search'   => false,
    'public'                => true,
    'has_archive'           => false,
    'publicly_queryable'    => true,
    'show_in_nav_menus'     => false,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'show_in_rest'          => true,
    'menu_icon'             => 'dashicons-calendar-alt',
    'menu_position'         => 33,
    'supports'              => array('title', 'revisions'),
    'yarpp_support'         => true
));

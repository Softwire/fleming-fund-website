<?php

register_post_type('organisation_types', array(
    'labels'                => array(
        'name' => __( 'Organisation Types' ),
        'singular_name' => __( 'Organisation Type' )
    ),
    'description'           => '',
    'exclude_from_search'   => false,
    'public'                => true,
    'publicly_queryable'    => true,
    'show_in_nav_menus'     => false,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'show_in_rest'          => true,
    'menu_icon'             => 'dashicons-building',
    'menu_position'         => 56,
    'supports'              => array('title', 'revisions')
));

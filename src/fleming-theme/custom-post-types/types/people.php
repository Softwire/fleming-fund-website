<?php

register_post_type('people', array(
    'labels'                => array(
        'name' => __( 'People' ),
        'singular_name' => __( 'Person' )
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
    'menu_icon'             => 'dashicons-admin-users',
    'menu_position'         => 41,
    'supports'              => array('title', 'revisions')
));

<?php

register_post_type('projects', array(
    'labels'                => array(
        'name' => __( 'Projects' ),
        'singular_name' => __( 'Project' )
    ),
    'description'           => '',
    'exclude_from_search'   => false,
    'public'                => true,
    'has_archive'           => true,
    'publicly_queryable'    => true,
    'show_in_nav_menus'     => false,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'show_in_rest'          => true,
    'menu_icon'             => 'dashicons-palmtree',
    'menu_position'         => 8,
    'supports'              => array('title', 'revisions')
));

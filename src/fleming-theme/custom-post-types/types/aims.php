<?php

register_post_type('aims', array(
    'labels'                => array(
        'name' => __( 'Aims' ),
        'singular_name' => __( 'Aim' )
    ),
    'description'           => '',
    'exclude_from_search'   => false,
    'publicly_queryable'    => false,
    'show_in_nav_menus'     => false,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'show_in_rest'          => true,
    'menu_icon'             => 'dashicons-palmtree',
    'menu_position'         => 0,
    'supports'              => array('title', 'revisions')
));

<?php

// Extracted from the `wp-cli` project. https://wp-cli.org/

$root = $_SERVER['DOCUMENT_ROOT'];
$path = '/'. ltrim( parse_url( urldecode( $_SERVER['REQUEST_URI'] ) )['path'], '/' );

if ( file_exists( $root.$path ) ) {

    // Enforces trailing slash, keeping links tidy in the admin
    if ( is_dir( $root.$path ) && substr( $path, -1 ) !== '/' ) {
        header( "Location: $path/" );
        exit;
    }

    // Runs PHP file if it exists
    if ( strpos( $path, '.php' ) !== false ) {
        chdir( dirname( $root.$path ) );
        require_once $root.$path;
    } else {
        return false;
    }
} else {
    if ( $path === '/about-us' || $path === '/about-us/' ) {
        header('Location: http://localhost:3000/about-us/our-aims/');
        exit;
    }

    if ( $path === '/our-approach' || $path === '/our-approach/' ) {
        header('Location: http://localhost:3000/our-approach/about-amr/');
        exit;
    }

    // Otherwise, run `index.php`
    chdir( $root );
    require_once 'index.php';
}
<?php

require_once __DIR__ . '/twig/autoload.php';
require_once 'twig.php';

$content = fleming_get_content();

if (isset($_GET["json"])) {
    header('Content-Type: application/json');
    echo(json_encode($content));
} else {
    // Do we include header and footer here? Might need to optionally omit them
    // Ditto need to set up the CSS path for header for all pages then.
    //
    // echo $twig->render('layout/header.html', $content);
    echo $twig->render('page-types/' . $template_name . '.html', $content);
    // echo $twig->render('layout/footer.html', $content);
}

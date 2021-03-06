<?php

require_once 'navigation/index.php';

$nav = get_home_nav();

$urlForPublicationType = $nav->getPublicationsLink()->getTarget() . '?type=' . get_post()->post_name;

if (get_post()->post_name == 'news') {
    $urlForPublicationType = $nav->getNewsAndEventsLink()->getTarget();
}

redirect_and_die($urlForPublicationType);
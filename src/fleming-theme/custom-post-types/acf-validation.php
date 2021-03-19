<?php

add_filter('acf/validate_value/name=flexible_content', 'require_overview', 10, 4);
add_filter('acf/validate_value/name=latitude', 'validate_latitude', 10, 4);
add_filter('acf/validate_value/name=longitude', 'validate_longitude', 10, 4);

function require_overview($valid, $value, $field, $input) {
    if (!$valid) {
        return $valid;
    }

    if ($_POST['post_type'] == "publications") {

        $publicationTypesThatRequireAnOverview = array("news", "publication", "case-study");

        $publicationTypeIdsThatRequireAnOverview  = array_map(
            function ($type) {
                return get_page_by_path($type, 'OBJECT', 'publication_types')->ID;
            },
            $publicationTypesThatRequireAnOverview
        );

        // The key for the 'type' field in publications.json.
        $publicationTypeId = $_POST['acf']['field_5b06a1224fa9e'];

        if (in_array($publicationTypeId, $publicationTypeIdsThatRequireAnOverview )) {
            foreach($value as $layout) {
                if ($layout['acf_fc_layout'] == "overview_text") {
                    return $valid;
                }
            }
            $valid = "News, Publication and Case study posts require an Overview";
        }
    }

    return $valid;
}

function validate_latitude($valid, $value, $field, $input) {
    if (!$valid) {
        return $valid;
    }

    $value = trim($value);

    if (is_numeric($value) && $value <= 90 && $value >= -90) {
        return $valid;
    }

    return "Latitude has to be a number between -90 and 90.";
}

function validate_longitude($valid, $value, $field, $input) {
    if (!$valid) {
        return $valid;
    }

    $value = trim($value);

    if (is_numeric($value) && $value <= 180 && $value > -180) {
        return $valid;
    }

    return "Longitude has to be a number between -180 and 180.";
}
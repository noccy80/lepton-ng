<?php

abstract class Content {

    function staticText($slug) {
        $db = new DatabaseConnection();
        $text = $db->getSingleRow("SELECT * FROM statictext WHERE slug=%s", $slug);
        if ($text) {
            return $text['content'];
        }
    }

}

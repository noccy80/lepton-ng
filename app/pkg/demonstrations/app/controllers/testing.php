<?php

class TestingController extends Controller {

    function index() {

        View::load('testing/filmstrip.php');

    }

    function menu() {

        $opts = array(
            'filmstrip'
        );
        foreach($opts as $opt) {
            $bl[] = sprintf('<a href="%s">%s</a>',$opt);
        }
        $body = 
        $h = sprintf('<div style="position:absolute; right:10px; top:0px; width:200px; background-color:#808080; color:#F0F0F0;">%s</div>', $body);

    }

    function filmstrip() {

        View::load('testing/filmstrip.php');

    }

}

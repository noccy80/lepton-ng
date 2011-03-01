<?php

    class LeptonInstaller {

        static function request($uri) {

            die($uri);

        }

    }

    Router::hookRequestUri("^\/install\/(.*)$", array('LeptonInstaller', 'request'));


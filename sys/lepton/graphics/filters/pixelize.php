<?php

    ModuleManager::load('lepton.graphics.filters');

    class PixelizeImageFilter extends ImageFilter {

        function __construct() {

            if (PHP_VERSION >= 503000) {
                // use internal
            } else {
                // roll our own
            }

        }

        function applyFilter($himage) {

        }

    }

?>

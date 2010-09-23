<?php
    //
    //  This is an example controller. You can modify this file as you see
    //  fit. For more information, see the documentation.
    //
    class DefaultController extends Controller {

        function index() {
            View::load('default/index.php');
        }

        function smarty() {
            View::load('index.tpl');
        }

    }

?>

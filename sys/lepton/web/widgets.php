<?php __fileinfo("Widgets for HTML");

interface IHtmlWidget {
    function __construct($params);
    function render();
}

abstract class HtmlWidget implements IHtmlWidget {
    static function insert($widget) {
        $widget->render();
    }
}


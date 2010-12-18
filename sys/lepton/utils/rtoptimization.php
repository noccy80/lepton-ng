<?php

abstract class RuntimeOptimization {
    const KEY_DBQUERYTIME = 'lepton.optimization.limits.dbquerytime';

    private static $_hints = array();
    private static $_enabled = false;
    private static $_icons = array(
        'warning' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAjBJREFUeNqkk0trE1EUx8/cO49OfGTSRNJMYsA0aVonoYh13YW71uJCKFQhKqibfgFLwYULsR/AhY+VG1d+C124kJiFIGipmoIZNUXtZDKTycz1njGpaRNU8MJv7txzzv/c5xEYY/A/TRQEAW5c5KwM+aKcR73/a5zvg84HT371wv07Apwuj0x+PZW/vArA4NO7x/f4+OGoIHLKAAiC/fBdHadSbCGZPTeTzC7OUElbQNvBOISMMnIqeqFSYs57mTkfZD1/qYS2f0rAZ5pVDmXnY/FSbn3jM6xvfAEtfjKnRDLz6BtK4PPPADi+ms6vGK71lti2DUintUVSJ84b6OvF7GlI4PNMPVgAZ49oxpyqRnXf+wGWZYX4ngWRiKYfPpqfw5hBjej7eweqCkSo6JOLhmd/hI7vQLVaBdM0YXt1FgK2CeJ40fCbmxUWsGc8vh3egtcFQPhyLsQnzpQJcbVmuw5mawtqtRo0Gg3wJQeY7ALIrqZEM2WM7esIPkROAgR5OZEpTTV3X4IXNEGiLnw1b4fItBNCBQuiqeQUA7qMGtSSLt8C38aVRLo47QVvVJFYoFAnJJG8FdIfI6rSVWMTx6ZRg1rS7UKeSspSMj2Wk+AbjPGZ+vTboA1JZbQcEcUl1Iq2zdZyxURBpruUMTzR38Vl79wM+9bO0/3vlwLVs+OF16/MNdFug/vi+Xadm+vDL/3uHyuR16Er4E3gKvEaOTLa/1LBuEQPF8hxfgowAINnMqTBUH7hAAAAAElFTkSuQmCC',
        'info' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAopJREFUeNqkk89rE0EUx9/sj2R/JatptU0pUk1tKrXUoogHFYUiFQMFT/4PrQevevUqQsGTd69FUfReBSkoaCn0lz+wsdGmaZtNdzfdnZ113uRXPXlw4O2+eTOf77z3dpbEcQz/MxTxGH0JQCQASeYz0guEFLhzkVt/c1+R2wLEMd8Y/wIW8RcDulhoCBwak4auTF84158fynX3WGbSxAT39w/clS/lax8//5zy/PAJ3/fmrwxacMbW7t8ujI6ziFl1L4DfpbJYsG07PXQyk84NZLIvXi/Zu3setESkBhtnDU2emZo8M+5Ua5bj7EO5XIa5R3lh6DuOC77rWTeuD47rSWkGmY4AiwpjZ7N5x6lZruuB7/sQBEE7NfQxhmsHvm8N5TJ5ZNolxBG91Ndr9VT3nDbEGIP8zTmIIgaKqkBEIxGXFZmXdLQHGT592ugBo/2yBEYURUD5xiovwXVdePbwBP8gBO48KIKkJBvFcgtDaiDTaSKjhFJK/AMK1Vqdn05AUpMC1nUd5IQGkqR2siMKQaYjENFiZcf33EC25IQJsgjVBWxZFigJg6eutQXCeuAhc6iJdGHjR2VLM9KQ0FLCFNUQsGmawm/F0fYqtTIyHYGIPt9Y/bbGc3NVLQ1qMiVORRjLQB9jaDQEd3ujuIpMp4SYlQLfm12cn7dHrk6M6qkjpqJZMHH3q1hOHTsl3l511115/26R1r1Zfu1LGCP4M+EpJDEIoF65pRrD9/qGx04fHxzpMuyM3gB3/K31pcrm8qe10Ft+DOH8qzhYB8E2BTCTLm7dIA30gnT+MpC+Ea6YbbatBPHmErAPb4F93+IBvOPbnKUtAeyF0TTlH38w3igXE+Ms+yPAAAHpKViFf4n+AAAAAElFTkSuQmCC',
        'error' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAlpJREFUeNqkU8tu2lAQHT8wtlEQcUKUIjVVgaiCVkhIlSq1isSKTdRNuu5P8AX5Alb9g+6zqZR8QNWmC3ZRa1UJIm0hAWpeNthg/OiMechl00UtHXvuvXPOnbn3mPF9H/7n4en1nmGAwy+BAUghTjB8iThY5v1EfMatzhB3Lg4Ib3FzfkPwdUSSKulCIZs6PFSkeFykCi1dL95dXx81rq7e2JZVxbwPf1WwIkuJxOmL4+Ocz/PSzHHgvtEIFhRFkfdzOTmZTu/ULi5OJ6MRrERYemFZKU4UK8VyOTcyTWk4HEKr1YLC+XkAimluPJ1Kz0qlHBuNVoizFsB+Tg7y+ezAMKQRqhuGAaZprkujmOZ0XQcDRfYymay7OKdFCw7Aq61kUtH6/TVpPB5Dp9MJSLfYiue6i555Hna3txXi4PDdSuChx7Kig3278zkYgwGYkwk0m02IRCLA4jy3Usb1qWmKxAlXAA4u2FQ6VuHjbhGcI3IsFgNh47Q5zHXCtzAH+GV0u0Vf02QpZCy1VAq+8Y27ntv2lDjrQ0S1T912u7eF/ck4lheGgpKqQrleD2I5BN2y+sQJC5zd9np1YFlLRldSUhQhCEKwYzRE9jzPas9mN8RZC3hoz4nrVi81TcUFS0KRJM5/yWQCUCwhbCTXxmPV9LwqcYjLkFUZJDzCwXN042OWreQEIftEEJQEx4mUNHTd6Xfb7qu2fdNAcg1d+IMMSNylAB3mDmIX7bWfBzjaA3iKV/dgabT7LsDXbwAfcVsM4TdCQ66zEmBDbfL/+IPJURMyKHK9PwIMAA7iHkoee771AAAAAElFTkSuQmCC'
    );

    public static function enable($state = true) {
        self::$_enabled = $state;
        if (!$state) self::$_hints = array();
    }

    public static function isEnabled() {
        return self::$_enabled;
    }

    public static function addHint($title,$code,$icon,$description) {
        self::$_hints[] = array(
            'title' => $title,
            'code' => $code,
            'icon' => (($icon==null)?'information':$icon),
            'description' => $description
        );
    }

    public static function insertReport() {
        if (count(self::$_hints)>0) {
            echo '<style type="text/css">';
            echo '#lepton-debug-el { position:absolute; z-index:9999999; left:50px; width:650px; top:50px; height:300px; background-color:#FFFFFF; background:rgba(255,255,255,0.7); padding:5px; border:solid 2px #C0C0C0; -moz-box-shadow:5px 5px 25px #000; }';
            echo '#lepton-debug-el hr { height:1px; color:#C0C0C0; background-color:#C0C0C0; border:solid 1px transparent; padding:0px; margin:10px 0px 10px 0px; }';
            echo '#lepton-debug-el h1 { margin:0px 0px 2px 0px; padding:0px; font:bold 12pt sans-serif; color:#404040; }';
            echo '#lepton-debug-el h2 { margin:2px 0px 2px 0px; padding:0px; font:bold 9pt sans-serif; color:#404040; }';
            echo '#lepton-debug-el p { margin:2px 0px 2px 0px; padding:0px; font:8pt sans-serif; color:#404040; }';
            echo '#lepton-debug-el p.id { margin:2px 0px 4px 0px; padding:0px; font:6pt sans-serif; color:#404040; }';
            echo '#lepton-debug-el pre { overflow-x:scroll; overflow-y:scroll; font-size:8pt; padding:5px; background-color:#F8F8F8; border:inset 1px #F0F0F0; }';
            echo '#lepton-debug-el a { color:#A06060; text-decoration:underline; font: 8pt sans-serif; text-decoration:none; }';
            echo '#lepton-debug-el a:hover { text-decoration:underline; }';
            echo '#lepton-debug-el input[type=button] { font:8pt sans-serif; color:#606060; }';
            echo '#lepton-debug-el input[type=submit] { font:8pt sans-serif; color:#202020; }';
            echo '</style>';
            echo '<div id="lepton-debug-el" style="overflow-y:hidden;">';
            echo '<h1>Optimization report</h1><hr>';
            foreach(self::$_hints as $hint) {
                echo sprintf('<div style="float:left;"><img src="%s"></div>',
                    (array_key_exists($hint['icon'],self::$_icons)?self::$_icons[$hint['icon']]:$hint['icon']));
                echo '<h2>'.$hint['title'].'</h2>';
                echo '<p class="id">'.$hint['modulecode'].'</p>';
                echo $hint['description'];
                echo '<hr>';
            }
            echo '</div>';
        }
    }
}

define('RTOPT',true);

config::def(RuntimeOptimization::KEY_DBQUERYTIME,1.0);

<?php

class WebDebugProvider implements IDebugProvider {

    function inspect($data,$table=false) {

        if (is_array($data)) {
            echo '<style type="text/css">';
            echo 'table { font:12px sans-serif; border-collapse:collapse; border:solid 1px #BBB; width:100%; }';
            echo 'th { text-align:left; padding:3px; border:solid 1px #BBB; background-color:#EEE; width:10%; }';
            echo 'td { padding:3px; border:solid 1px #BBB}';
            echo '</style>';
            if ($table) {
                echo self::inspectTable($data);
            } else {
                echo self::inspectArray($data);
            }
        }
    }

    static function inspectArray($data) {
        $ret = '<table>';
        foreach ($data as $key => $value) {
            $ret.='<tr><th>' . htmlentities($key) . '</th><td>';
            if (is_array($value)) {
                $ret.= self::inspectArray($value);
            } else {
                $ret.= htmlentities($value);
            }
            $ret.='</td></tr>';
        }
        $ret.= '</table>';
        return $ret;
    }

    static function inspectTable($data) {
        $skipnum = true;
        $ret = '<table class="inspect-table">';
        $head = $data[0];
        $ret.= '<tr>';
        $ret.= '<th>&nbsp;</th>';
        $keys = array_keys($head);
        for($idx = 0; $idx < count($keys); $idx+=2) {
            $col = $keys[$idx];
            $ret.='<th>'.$col.'</th>';
        }
        $ret.= '</tr>';
        $idx = 0;
        for($rowidx = 0; $rowidx < count($data); $rowidx++) {
            $ret.='<tr><th>' . $rowidx . '</th>';
            $row = $data[$rowidx]; $rowdata = array_values($row);
            for ($col = 0; $col < (count($row) / 2); $col++) {
                $value = $row[$col];
                if (is_array($value)) {
                    $ret.= '<td>'.debug::inspectTableArray($value).'</td>';
                } else {
                    $ret.= '<td>'.htmlentities($value).'</td>';
                }
            }
            $ret.='</tr>';
        }
        $ret.= '</table>';
        return $ret;
    }

}

debug::setDebugProvider(new webDebugProvider());

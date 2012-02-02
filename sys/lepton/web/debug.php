<?php

class WebDebugProvider implements IDebugProvider {

    function inspect($data,$table=false) {

        if (is_array($data)) {
            echo '<style type="text/css">';
            echo 'table { font:12px sans-serif; border-collapse:collapse; border:solid 1px #BBB; width:100%; margin:1px; }';
            echo 'th { text-align:left; padding:3px; border:solid 1px #BBB; background-color:#EEE; width:10%; }';
            echo 'td { padding:3px; border:solid 1px #BBB}';
            echo '</style>';
            if ($table) {
                echo self::inspectTable((array)$data);
            } else {
                echo self::inspectArray((array)$data);
            }
        }
    }

    static function inspectArray($data) {
        $ret = '<table>';
        foreach ($data as $key => $value) {
            $ret.='<tr><th>' . htmlentities($key) . ' <br><em style="font-size:10px; font-weight:normal">'.typeOf($value).'</em></th><td>';
            if (is_array($value) || is_a($value, 'StdClass')) {
                $ret.= self::inspectArray((array)$value);
            } else {
                if ($value === null) {
                    $ret.= '<img src="data:image/gif;base64,R0lGODdhEwAHAKECAAAAAPj4/////////ywAAAAAEwAHAAACHYSPmWIB/KKBkznIKI0iTwlKXuR8B9aUXdYprlsAADs=">';
                } else {
                    $ret.= htmlentities($value);
                }
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
                    if ($value === null) {
                        $ret.= '<td><img src="data:image/gif;base64,R0lGODdhEwAHAKECAAAAAPj4/////////ywAAAAAEwAHAAACHYSPmWIB/KKBkznIKI0iTwlKXuR8B9aUXdYprlsAADs="></td>';
                    } else {
                        $ret.= '<td>'.htmlentities($value).'</td>';
                    }
                }
            }
            $ret.='</tr>';
        }
        $ret.= '</table>';
        return $ret;
    }
   
    public function inspectReflection($obj) {
        
        $rc = new ReflectionClass($obj);
        
        var_dump($rc);
        
        die();
        
    }
    
}

debug::setDebugProvider(new webDebugProvider());

<?php

class HostsAction extends Action {

    public static $actions = array(
        'match' => array(
            'arguments' => '[\g{pattern}]',
            'info' => 'List available host groups'
        )
    );

    function match($pattern='*') {
        $hf = fopen('/etc/hosts','r');
        console::writeLn(__astr('\b{%-20s %-15s %-6s %s}'), 'Groupname','Target', 'Act','Hosts');
        $groupname = null;
        $hosts = array();
        while(!feof($hf)) {
            $h = fgets($hf);
            $h = str_replace("\r","",$h);
            $h = str_replace("\n","",$h);
            $h = trim($h);
            if (strlen($h) > 0) {
                if ($h[0] == '#') {
                    $c = explode(' ',trim(substr($h,1)));
                    if ($c[0] == 'GROUP') {
                        $groupname = $c[1];
                        $active = ($c[2] == 'ENABLED');
                    } elseif ($c[0] == 'ENDGROUP') {
                        $hostlist = array();
                        foreach($hosts as $host) if (strlen(trim($host))>0) $hostlist[] = trim($host);
                        if (count(array_unique($targets)) == 1) { $target = $targets[0]; } else { $target = 'Multiple'; }
                        console::writeLn('%-20s %-15s %-6s %s', $groupname, $target, ($active)?'Yes':'No', join(', ',$hostlist));
                        $groupname = null;
                        $hosts = array();
                        $targets = array();
                    } elseif ($groupname != null) {
                        $hosts = array_merge($hosts,array_slice($c,1));
                        $targets[] = $c[0];
                    }
                } elseif ($groupname != null) {
                    $c = explode(' ',trim($h));
                    $hosts = array_merge($hosts,array_slice($c,1));
                    $targets[] = $c[0];
		}
            }
        }
    }

    function enable($group=null) {

        if (!$group) { console::fatal("You need to specify the group to enable!"); exit(1); }
        if (!is_writable('/etc/hosts')) { console::fatal("You need to run this script as root!"); exit(1); }

        file_put_contents('/etc/hosts.bak',file_get_contents('/etc/hosts'));
        $fin = explode("\n",file_get_contents('/etc/hosts'));
        $fout = array();
        $ingroup = false;
        $enablegroup = false;
        foreach($fin as $row) {
            $row = trim(str_replace("\t"," ",$row));
            // Remove double spaces
            while(strpos($row,"  ")) $row = str_replace("  "," ",$row);
            // Check the statement
            $rdo = $row;
            if (strlen($row)>0) {
                if ($row[0] == '#') {
                    $rd = explode(' ',trim(substr(trim($row),1)));
                    if ($rd[0] == 'GROUP') {
                        $ingroup = true;
                        if (($rd[1] == $group) && ($rd[2] == 'DISABLED')) {
                            $rdo = '#GROUP '.$rd[1].' ENABLED';
                            $enablegroup = true;
                        }
                    } elseif ($rd[0] == 'ENDGROUP') {
                        $ingroup = false;
                        $enablegroup = false;
                        $rdo = "#ENDGROUP";
                    } elseif (($ingroup) && ($enablegroup)) {
                        $rdo = sprintf("%-20s %s", $rd[0], join(' ',array_slice($rd,1)));
                    } else {
                        if (preg_match('/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/',$rd[0])) {
                            $rdo = sprintf("#%-19s %s", $rd[0], join(' ',array_slice($rd,1)));
                        } else {
                            $rdo = sprintf("# %s", join(' ',$rd));
                        }
                    }
                } else {
                    $rd = explode(' ',trim($row));
                    $rdo = sprintf("%-20s %s", $rd[0], join(' ',array_slice($rd,1)));
                }
            }
            $fout[] = $rdo;
        }
        $fsout = join("\n", $fout);
        file_put_contents('/etc/hosts',$fsout);

    }

    function disable($group=null) {

        if (!$group) { console::fatal("You need to specify the group to disable!"); exit(1); }
        if (!is_writable('/etc/hosts')) { console::fatal("You need to run this script as root!"); exit(1); }

        file_put_contents('/etc/hosts.bak',file_get_contents('/etc/hosts'));
        $fin = explode("\n",file_get_contents('/etc/hosts'));
        $fout = array();
        $ingroup = false;
        $enablegroup = false;
        foreach($fin as $row) {
            $row = trim(str_replace("\t"," ",$row));
            // Remove double spaces
            while(strpos($row,"  ")) $row = str_replace("  "," ",$row);
            // Check the statement
            $rdo = $row;
            if (strlen($row)>0) {
                if ($row[0] == '#') {
                    $rd = explode(' ',trim(substr(trim($row),1)));
                    if ($rd[0] == 'GROUP') {
                        $ingroup = true;
                        if (($rd[1] == $group) && ($rd[2] == 'ENABLED')) {
                            $rdo = '#GROUP '.$rd[1].' DISABLED';
                            $disablegroup = true;
                        }
                    } elseif ($rd[0] == 'ENDGROUP') {
                        $ingroup = false;
                        $disablegroup = false;
                        $rdo = "#ENDGROUP";
                    } elseif (($ingroup) && ($disablegroup)) {
                        $rdo = sprintf("#%-20s %s", $rd[0], join(' ',array_slice($rd,1)));
                    } else {
                        if (preg_match('/[0-9]*\.[0-9]*\.[0-9]*\.[0-9]*/',$rd[0])) {
                            $rdo = sprintf("#%-19s %s", $rd[0], join(' ',array_slice($rd,1)));
                        } else {
                            $rdo = sprintf("# %s", join(' ',$rd));
                        }
                    }
                } else {
                    $rd = explode(' ',trim($row));
                    $rdo = sprintf("%-20s %s", $rd[0], join(' ',array_slice($rd,1)));
                }
            }
            $fout[] = $rdo;
        }
        $fsout = join("\n", $fout);
        file_put_contents('/etc/hosts',$fsout);

    }

    function addgroup($group=null) {

        if (!$group) { console::fatal("You need to specify the group to add!"); exit(1); }

    }

}

actions::register(
	new HostsAction(),
	'hosts',
	'Manage hosts file groups for local development',
	HostsAction::$actions
);

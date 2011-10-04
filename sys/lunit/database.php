<?php

class LunitDatabaseLogger {

    private $db = null;
    private $sessionid = null;
    
	function __construct($table) {

		$dbs = config::get('lunit.database', null);
		if ($dbs) {
			$db = new DatabaseConnection($dbs);
		} else {
			$db = new DatabaseConnection();
		}
		$this->db = $db;
        $this->sessionid = $this->db->insertRow("INSERT INTO lunitsessions (completed) VALUES (NOW())");

	}
    
    function onCaseBegin($name,$meta) { }
    function onTestBegin($name,$meta) { }
    function onTestEnd($state,$message) { }
    
    function onCaseEnd($results) {
        
        $name = $results['meta']['description'];
        $case = $results['meta']['casename'];
        $tests = $results['tests'];
        
        $rs = $this->db->getRows("SELECT * FROM lunitcases WHERE casekey=%s", $case);
        if (!$rs) {
            $caseid = $this->db->insertRow("INSERT INTO lunitcases (casekey,description) VALUES (%s,%s)", $case, $name);
        } else {
            $caseid = $rs['id'];
        }
        
        foreach($tests as $key=>$test) {
            $testdesc = $test['meta']['description'];
            $testrepeat = $test['meta']['repeat'];
            $trs = $this->db->getRows("SELECT * FROM lunittests WHERE caseid=%d AND testkey=%s", $caseid, $key);
            if (!$trs) {
                $testid = $this->db->insertRow("INSERT INTO lunittests (caseid,testkey,description,repeatcount) VALUES (%d,%s,%s,%d)", $caseid, $key, $testdesc, $testrepeat);
            } else {
                $testid = $trs['id'];
            }
            
            if ($test['skipped']) {
                $result = 'SKIPPED';
            } else {
                if ($test['passed']) {
                    $result = 'PASSED';
                } else {
                    $result = 'FAILED';
                }
            }
            $timemax = $test['minmax'][1];
            $timemin = $test['minmax'][0];
            $timeavg = $test['average'];
            $timeela = $test['elapsed'];
            $message = $test['message'];
            $this->db->insertRow(
                "INSERT INTO lunitresults ".
                "(testid,caseid,sessionid,result,timemin,timemax,timeavg,timeela,message) ".
                "VALUES ".
                "(%d,%d,%d,%s,%.2f,%.2f,%.2f,%.2f,%s)",
                $testid,$caseid,$this->sessionid,$result,$timemin,$timemax,$timeavg,$timeela,$message
            );
        }
        
    }


}

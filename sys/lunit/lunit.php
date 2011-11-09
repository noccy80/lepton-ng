<?php

using('lunit.lunitcase');
using('lepton.math');

interface ILunitStatusCallback {
    function onCaseBegin($name,$meta);
    function onCaseEnd();
    function onTestBegin($name,$meta);
    function onTestEnd($status,$message);
}

abstract class Lunit {
    private static $cases = array();
    public static function register($case) {
        self::$cases[] = $case;
    }
    public static function getCases() {
        return self::$cases;
    }
}

class LunitRunner {

    private $statuscb = null;
    private $results = null;
    private $dblog = null;

    function setStatusCallback(ILunitStatusCallback $object) {
        $this->statuscb = $object;
    }

    function setDatabaseLogger(LunitDatabaseLogger $logger) {
        $this->dblog = $logger;
    }

    function getResults() {

        return $this->results;

    }

    function run() {

        $cases = Lunit::getCases();
        $casedata = array();
        // Enumerate the cases
        foreach($cases as $case) {
            // Setup report structure
            $casereport = array();
            // Reflect the class to find methods and metadata
            $r = new ReflectionClass($case);
            $ml = $r->getMethods();
            $skip = false;
            $meta = LunitUtil::parseDoc($r->getDocComment());
            if (!isset($meta['description'])) $meta['description'] = $case;
            $meta['casename'] = $case;
            if (isset($meta['extensions'])) {
                $extn = explode(' ',$meta['extensions']);
                foreach($extn as $ext) {
                    if (!extension_loaded($ext)) { $skip = true; $skipmsg = "Need extension: ".$ext; }
                }
            }

            $casereport['meta'] = $meta;
            // Callback if set
            if ($this->statuscb) $this->statuscb->onCaseBegin($case,$meta);
            if ($this->dblog) $this->dblog->onCaseBegin($case,$meta);
            try {
                if (!$skip) $tc = new $case($this);
                foreach($ml as $method) {
                    $methodname = $method->getName();
                    if ($method->isPublic() && (substr($methodname,0,1) != '_')) {
                        $methodreport = array();
                        $tmeta = LunitUtil::parseDoc($method->getDocComment());
                        if (!isset($tmeta['description'])) $tmeta['description'] = $methodname;
                        if (!isset($tmeta['repeat'])) $tmeta['repeat'] = 1;

                        // Save meta to method report
                        $methodreport['meta'] = $tmeta;
                        // Times to repeat the test
                        $repeat = intval($tmeta['repeat']);
                        // Callback if set, then create timer
                        if ($this->statuscb) $this->statuscb->onTestBegin($methodname,$tmeta);
                        if ($this->dblog) $this->dblog->onTestBegin($methodname,$meta);
                        $methodreport['skipped'] = false;
                        $tavg = null; $tmax = null; $tmin = null;
                        if (!$skip) {
                            $tm = new Timer();
                            try {
                                $telapsed = array();
                                $ttotal = 0;
                                for($n = 0; $n < $repeat; $n++) {
                                    $tm->start();
                                    $tc->{$methodname}();
                                    $tm->stop();
                                    $telapsed[] = $tm->getElapsed() * 1000;
                                    $ttotal += $tm->getElapsed() * 1000;
                                }
                                $ttot = math::sum($telapsed);
                                $tavg = math::average($telapsed);
                                $tmin = math::min($telapsed);
                                $tmax = math::max($telapsed);
                                $tdev = math::deviation($telapsed);
                                $methodreport['passed'] = true;
                                $methodreport['message'] = null;
                                if ($repeat>1) {
                                    console::write('%6.1fms <%6.1fms> %6.1fms ', $tmin, $tavg, $tmax);
                                } else {
                                    console::write('%6.1fms ', $tmax);
                                }
                                if ($this->statuscb) $this->statuscb->onTestEnd(true,null);
                                if ($this->dblog) $this->dblog->onTestEnd(true,null);
                            } catch (LunitAssertionFailure $f) {
                                $tm->stop();
                                $methodreport['passed'] = false;
                                $methodreport['message'] = $f->getMessage();
                                if ($this->statuscb) $this->statuscb->onTestEnd(false,$f->getMessage());
                                if ($this->dblog) $this->dblog->onTestEnd(false,$f->getMessage());
                            } catch (LunitAssertionSkip $f) {
                                $tm->stop();
                                $methodreport['passed'] = false;
                                $methodreport['skipped'] = true;
                                $methodreport['message'] = 'Skipped';
                                if ($this->statuscb) $this->statuscb->onTestEnd(null,$f->getMessage());
                                if ($this->dblog) $this->dblog->onTestEnd(null,$f->getMessage());
                            } catch (Exception $e) {
                                $tm->stop();
                                $methodreport['passed'] = false;
                                $methodreport['message'] = $e->getMessage();
                                if ($this->statuscb) $this->statuscb->onTestEnd(false,$e->getMessage());
                                if ($this->dblog) $this->dblog->onTestEnd(false,$f->getMessage());
                            }
                        } else {
                            $methodreport['passed'] = false;
                            $methodreport['skipped'] = true;
                            $methodreport['message'] = $skipmsg;
                            $this->statuscb->onTestEnd(null,$skipmsg);
                            if ($this->dblog) $this->dblog->onTestEnd(null,$skipmsg);
                        }
                        $methodreport['elapsed'][] = $tm->getElapsed();
                        $methodreport['average'] = $tavg;
                        $methodreport['minmax'] = array($tmin,$tmax);
                        // Save report
                        $casereport['tests'][$methodname] = $methodreport;
                    }
                }
            } catch(Exception $e) {
                console::writeLn("Skipped due to exception: %s", $e->getMessage());
            }
            
            $casedata[$case] = $casereport;

            // Callback if set
            if ($this->statuscb) $this->statuscb->onCaseEnd();
            if ($this->dblog) $this->dblog->onCaseEnd($casereport);

        }
        
        $this->results = $casedata;
        
    }
    
}

class LunitUtil {
    function parseDoc($str) {
        $se = explode("\n",$str);
        $se = array_slice($se,1,count($se)-2);
        $cur = null;
        $ret = array();
        foreach($se as $row) {
            $lnclear = trim(substr(trim($row),1));
            if ($lnclear[0] == '@') {
                $cur = substr($lnclear,1,strpos($lnclear,' ')-1);
                $data = substr($lnclear,strpos($lnclear,' ')+1);
                $ret[$cur] = $data;
            } else {
                $ret[$cur].= $lnclear;
            }
        }
        return $ret;
    }
}

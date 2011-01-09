<?php

////////// Duration ///////////////////////////////////////////////////////////

	/**
	 * Generic duration class. Takes a string and turns it into a number.
	 * The string can be specified as "5d" for 5 days, "2:30" or "2h 30m"
	 * for 2 hours 30 minutes etc.
	 */
	abstract class Duration {

		static function toSeconds($str) {
			if (is_string($str)) {
			    if (preg_match('/[0-9]*\:[0-9]*/', $str)) {
				    $strspan = explode(':',$str);
				    $secs = time();
				    $neg = false;

				    if (count($strspan)>0) { // Secs
				        $_secs = intval($strspan[count($strspan)-1]);
				        $secs+= $_secs;
				    }
				    if (count($strspan)>1) { // Minutes
				        $_mins = intval($strspan[count($strspan)-2]);
				        $secs+= ($_mins * 60);
				    }
				    if (count($strspan)>2) { // Hours
				        $_hour = intval($strspan[count($strspan)-3]);
				        $secs+= ($_hour * 60 * 60);
				    }

			    } else {
				    $str = trim($str);
				    // Split and parse all items
				    // Use ! for absolute time
				    $strspan = explode(' ',$str);
				    $secs = time();
				    $neg = false;
				    foreach($strspan as $s) {
					    switch(strToLower(substr($s,strlen($s) - 1,1))) {
						    case '!':
							    $secs = 0;
							    break;
						    case '-':
							    $secs = 0;
							    $neg = true;
						    case 'w': // Week
							    $secs+= (60 * 60 * 24 * 7 * (int)$s);
							    break;
						    case 'd': // Days
							    $secs+= (60 * 60 * 24 * (int)$s);
							    break;
						    case 'h': // Hours
							    $secs+= (60 * 60 * (int)$s);
							    break;
						    case 'm': // Minutes
							    $secs+= (60 * (int)$s);
							    break;
						    case 's': // Seconds
							    $secs+= (int)$s;
							    break;
						    default: // [[hh:]mm:]ss
					    }
				    }
                }
				if ($neg) return (time() - $secs);
				return $secs;
			} else {
				return $str;
			}
		}

		static function toMinutes($str) {
			if (is_string($str)) {
				return floor(duration::toSeconds($str)/60);
			} else {
				return $str;
			}
		}

	}

////////// Timestamp //////////////////////////////////////////////////////////

	class Timestamp {

		private $_time = null;

		function __construct($time=null) {
			if ($time) {
				if (is_string($time)) {
					$this->_time = strtotime($time);
				} elseif( is_int($time)) {
					$this->_time = $time;
				}
			} else {
				$this->_time = time();
			}
		}

		function format($format,$time=null) {
			if ($time) {
				$d = new Timestamp($time);
				return $d->format($format);
			}
			return date($format, $this->_time);
		}

		/**
		 * Calculates the time past since an event occured, f.ex. "2 days ago".
		 * Taken from the php.net website.
		 *
		 * @author andypsv <andypsv@rcdrugs.com>
		 */
		function ago($time=null) {
			if ($time) {
				$d = new Timestamp($time);
				return $d->ago();
			}
			$tm = $this->_time;
		    $cur_tm = time(); $dif = $cur_tm-$tm;
		    $pds = array('second','minute','hour','day','week','month','year','decade');
		    $pdsp = array('seconds','minutes','hours','days','weeks','months','years','decades');
			if (modulemanager::has('i18n')) {
				foreach($pds as &$pd) $pd = intl::str($pd);
				foreach($pdsp as &$pd) $pd = intl::str($pd);
			}
		    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
		    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

		    $no = floor($no); if($no <> 1) $pds[$v] = $pdsp[$v]; $x=sprintf("%d %s ",$no,$pds[$v]);
		    if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
		    return $x;

		}

		function add($time) {
			return new Timestamp($this->_time + strtotime($time, $this->_time));
		}

		function toGMT(DateTimeZone $dtz) {

		}

		function fromGMT(DateTimeZone $dtz) {

		}

	}

////// Calendar ///////////////////////////////////////////////////////////////



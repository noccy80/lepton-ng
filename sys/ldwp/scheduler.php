<?php

	/**
	 * @brief Creates and maintains a schedule for a task
	 *
	 * This class provides several ways of defining a schedule including cron
	 * style strings:
	 *
	 *	minute hour dayofweek month dayofmonth
	 *
	 * All 5 of these fields should be defined using one of the following
	 * patterns:
	 *
	 *	*	 Every occasion (i.e. any minute, any hour etc)
	 *	*\n      Every n minutes (note: forward slash following asterisk)
	 *	n	 On a specific occasion (i.e. hour, minut etc)
	 *	n,n,.    On the occasions n
	 */
	class Schedule {
		function __construct() {
			$args = func_get_args();
			if (count($args)>0) {
				// Match cron style query (1 * * * *) m/h/dow/mon/dom
				//
				$cronptn = "([\*]|[\*\/[\d]*]?|[\d]*|[\d,]*)";
				if (preg_match('/^'.join('[[:space:]]',array($cronptn,$cronptn,$cronptn,$cronptn,$cronptn)).'$/', $args[0], $ret)) {
					list($exp,$min,$hour,$dow,$mon,$dom) = $ret;
				}
				// Match string (Mon,Tue,Wed 18:00)
				Console::writeLn("Month:%s Hour:%s Min:%s DayOfWeek:%s DayOfMonth:%s", $mon, $hour, $min, $dow, $dom);
			}
		}
	}

	class LdwpScheduler {

		function addTask(Schedule $schedule, LdwpJob $task) {

		}

	}

?>

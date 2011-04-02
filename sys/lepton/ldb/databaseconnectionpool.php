<?php

using('lepton.ldb.databasedriver');

abstract class DatabaseConnectionPool {

	private static $pool = array();

	public static function getPooledConnection($connectionstring) {
		$csh = md5(serialize($connectionstring));	
		if (!arr::hasKey(self::$pool,$csh)) {
			$driverclass = DatabaseDriver::getDriverClass($connectionstring['driver']);
			self::$pool[$csh] = array(
				'instance' => new $driverclass($connectionstring),
				'count' => 0
			);
			self::$pool[$csh]['instance']->setPoolIdentity($csh);
		}
		self::$pool[$csh]['count']++;
		return self::$pool[$csh]['instance'];
	}
	
	public static function freePooledConnection(DatabaseDriver $connection) {
		$pi = $connection->getPoolIdentity();
		self::$pool[$pi]['count']--;
		if (self::$pool[$pi]['count'] == 0) {
			unset(self::$pool[$pi]);
		}
	}

}

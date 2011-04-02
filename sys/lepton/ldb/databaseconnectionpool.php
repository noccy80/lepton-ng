<?php

using('lepton.ldb.databasedriver');

/**
 * @brief Datbase instance manager for singleton approach
 *
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>>
 */
abstract class DatabaseConnectionPool {

	private static $pool = array();

	/**
	 * @brief Return a pooled connection for the connectionstring
	 *
	 * @param mixed $connectionstring The connectionstring to request
	 * @return DatabaseDriver the database driver
	 */
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
	
	/**
	 * @brief Free a connection when the instance count hits zero.
	 *
	 * @param Databasedriver $connection The connection to free
	 */
	public static function freePooledConnection(DatabaseDriver $connection) {
		$pi = $connection->getPoolIdentity();
		self::$pool[$pi]['count']--;
		if (self::$pool[$pi]['count'] == 0) {
			unset(self::$pool[$pi]);
		}
	}

}

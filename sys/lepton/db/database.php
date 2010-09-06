<?php

	define('LEPTON_DB_PREFIX','l2');

	class DatabaseException extends BaseException { }

	abstract class DatabaseManager {

		static $pool = array();
		static function getConnection($group='default') {

			if (!isset(self::$pool[$group])) {
				Console::debugEx(LOG_DEBUG1,__CLASS__,"PoolConnectGroup(%s)", $group);		
				self::$pool[$group] = self::poolConnectGroup($group);
			}
			return self::$pool[$group];
			
		}
		
		static function poolConnectGroup($group) {
			$cfg = config::get('lepton.db.'.$group);
			$driver = explode('/',$cfg['driver']);
			if ($driver[0] != '') {
				$class = $driver[0].'DatabaseDriver';
				Console::debugEx(LOG_DEBUG1,__CLASS__,"Base DBM driver: %s (%s) - %s", $class, $driver[0], $cfg['driver']);		
				return new $class($cfg);
			} else {
				throw new DatabaseException("No database driver configured");
			}
		}

		static function getPooledConnection() {

			// Mangle pool and return a connection

		}

	}


	interface IDatabaseDriver {
		function connect();
		function disconnect();
		function escapeString($args);
		function query($sql);
	}

	abstract class DatabaseDriver implements IDatabaseDriver { }

	class PdoDatabaseDriver extends DatabaseDriver {

		private $conn;

		function __construct($cfg) {
			$driver = explode('/',$cfg['driver']);
			$drv = $driver[1];

			$user = $cfg['username'];
			$pass = $cfg['password'];
			
			Console::debugEx(LOG_DEBUG1,__CLASS__,"Connecting with driver %s.", $drv);		
			switch($drv) {
				case 'sqlite':
					printf($cfg['filename']);
					$dsn = 'sqlite:'.$cfg['filename'];
					$user = null;
					$pass = null;
					break;
				case 'mysql':
					$dsn = 'mysql:';
					$db = $cfg['database'];
					if (isset($cfg['socket']) && ($cfg['socket'] != null)) {
						$dsn.='unix_socket='.$cfg['socket'].';dbname='.$db;
					} else {
						$host = $cfg['hostname'];
						// $port = $cfg['port'];
						$dsn.='host='.$host.';dbname='.$db;
					}
					break;
			}
			Console::debugEx(LOG_DEBUG1,__CLASS__,"Connection DNS: %s.", $dsn);
			try {
				$this->conn = new PDO($dsn,$user,$pass);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				throw new BaseException("Could not connect to database type '".$cfg['driver']."'.");
			}
		}

		function __destruct() {

			Console::debugEx(LOG_LOG,__CLASS__,"Closing handle (querycount = %d)", Database::$querycounter);

		}

		function connect() {

		}
		
		function disconnect() {
		
		}
		
		function escapeString($args) {
			for($n = 1; $n < count($args); $n++) {
				$args[$n] = $this->conn->quote($args[$n]);
			}
			$str = call_user_func_array("sprintf",$args);
			return $str;
		}

		function exec($sql) {
			Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Exec: %s", $sql);
			$query = $this->conn->exec($sql);
		}

		function query($sql) {
			Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Query: %s", $sql);
			$query = $this->conn->query($sql);
			if ($query) {
				$ret = array(
					'data' => $query->fetchAll(),
					'count' => $query->rowCount(),
					'columns' => $query->columnCount(),
					'error' => false
				);
			} else {
				$ei = $this->conn->errorInfo();
				Console::warn("Database error: %s (%s)", $ei[2], $ei[0]);
				$ret = array(
					'data' => null,
					'count' => 0,
					'columns' => 0,
					'error' => $ei[2]
				);
			}
			return $ret;
		}
	
	}

	final class DatabaseConnection {
	
		private $db_conn;
		
		function __construct($set='default') {

			Console::debugEx(LOG_DEBUG1,__CLASS__,"Initializing connection for %s.", $set);		
			$this->db_conn = DatabaseManager::getConnection($set);
			
		}

		function getRows($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Console::debugEx(LOG_DEBUG1,__CLASS__,"GetRows: %s", $sql);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);
			
			return $queryresult['data'];
		
		}
		
		function getSingleRow($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleRow: %s", $sql);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);
			
			// Only return first item
			if ($queryresult->count > 0) {
				return $queryresult['data'][0];
			} else {
				return null;
			}
		
		}
		
		function getSingleValue($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleValue: %s", $sql);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);
			
			// Only return first column of first item
			if ($queryresult->count > 0) {
				return $queryresult['data'][0][0];
			} else {
				return null;
			}
		
		}
		
		function insertRow($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Console::debugEx(LOG_DEBUG1,__CLASS__,"InsertRow: %s", $sql);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);

			return $queryresult->autonumber;
		
		}
		
		function updateRow($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Console::debugEx(LOG_DEBUG1,__CLASS__,"UpdateRow: %s", $sql);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);

			return null;
		
		}
		
		function execute($pattern,$vars=null) {

			$args = func_get_args();
			$sql = $this->db_conn->escapeString($args);
			Database::$querycounter++;
			$queryresult = $this->db_conn->query($sql);

			return null;
		
		}
		
		function transactionBegin() {
			return false;
		}
		
		function transactionEnd() {
			return false;
		}
		
		
	
	}
	
	class Database {
		static $querycounter = 0;
	}

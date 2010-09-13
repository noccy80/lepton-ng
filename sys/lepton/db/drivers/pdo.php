<?php __fileinfo("PDO Databaase Driver");

	class PdoDatabaseDriver extends DatabaseDriver {

		private $conn;

		private $host;
		private $db;
		private $user;
		private $pass;
		private $dsn;
		public $autonumber;

		function __construct($cfg) {
			$driver = explode('/',$cfg['driver']);
			$drv = $driver[1];

			switch($drv) {
				case 'sqlite':
					printf($cfg['filename']);
					$this->dsn = 'sqlite:'.$cfg['filename'];
					$this->user = null;
					$this->pass = null;
					break;
				case 'mysql':
					$this->dsn = 'mysql:';
					$this->db = $cfg['database'];
					$this->user = $cfg['username'];
					$this->pass = $cfg['password'];
					if (isset($cfg['socket']) && ($cfg['socket'] != null)) {
						$this->dsn.='unix_socket='.$cfg['socket'].';dbname='.$this->db;
					} else {
						$this->host = $cfg['hostname'];
						// $port = $cfg['port'];
						$this->dsn.='host='.$this->host.';dbname='.$this->db;
					}
					break;
			}
			Console::debugEx(LOG_DEBUG1,__CLASS__,"Connection DSN: %s.", $this->dsn);
			try {
				$this->conn = new PDO($this->dsn,$this->user,$this->pass);
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				if (isset($this->db)) $this->exec("USE ".$this->db);
			} catch (PDOException $e) {
				throw new BaseException("Could not connect to database type '".$cfg['driver']."'. ".$e->getMessage());
			}
		}

		function __destruct() {

			Console::debugEx(LOG_DEBUG1,__CLASS__,"Closing handle after %d queries (%d querying, %d updating, %d executing)", Database::$counter, Database::$queries['QUERYING'], Database::$queries['UPDATING'], Database::$queries['EXECUTING']);

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
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$query = $this->conn->query($sql);
			if ($query) {
				if ($query->rowCount() > 0) {
					try {
						$ret = array(
							'data' => $query->fetchAll(),
							'count' => $query->rowCount(),
							'columns' => $query->columnCount(),
							'error' => false
						);
					} catch (PDOException $e) {
						$ret = array(
							'data' => null,
							'count' => $query->rowCount(),
							'columns' => null,
							'error' => false
						);
					}
				} else {
					$ret = array(
						'data' => null,
						'count' => $query->rowCount(),
						'columns' => null,
						'error' => false
					);
				}
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
			$this->autonumber = $this->conn->lastInsertId();
			return $ret;
		}
	
	}


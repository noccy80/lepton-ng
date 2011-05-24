<?php __fileinfo("PDO Databaase Driver");

using('lepton.ldb.databasedriver');

class PdoDatabaseDriver extends DatabaseDriver {

    private $conn;

    private $driver;
    private $host;
    private $db;
    private $user;
    private $pass;
    private $dsn;
    public $autonumber;

    function __construct(array $config) {
        $driver = explode('::',str_replace('/','::',$config['driver']));
        $drv = $driver[1];
        $cfg = $config;

        switch($drv) {
            case 'sqlite':
                $this->dsn = 'sqlite:'.$cfg['filename'];
                $this->user = null;
                $this->pass = null;
				$this->driver = 'sqlite';
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
                $this->driver = 'mysql';
                break;
        }
        Console::debugEx(LOG_DEBUG1,__CLASS__,"Connection DSN: %s.", $this->dsn);
        try {
            $this->conn = new PDO($this->dsn,$this->user,$this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $cs = config::get('lepton.charset');
            $cs = str_replace('utf-','utf',$cs); // 'utf8';
            // $this->exec("CHARSET ".$cs);
            if ($this->driver == 'mysql') {
                if (isset($this->db) && ($this->db['database'] != '')) $this->execute("USE ".$this->db);
                $this->execute("SET NAMES '".$cs."'");
                $this->execute("SET character_set_results='".$cs."'");
                $this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Could not connect to database type '".$cfg['driver']."'. ".$e->getMessage());
        }
    }
    
    function __destruct() {

        Console::debugEx(LOG_DEBUG1,__CLASS__,"Closing handle after %d queries (%d querying, %d updating, %d executing)", Database::$counter, Database::$queries['QUERYING'], Database::$queries['UPDATING'], Database::$queries['EXECUTING']);

    }

    function quote($args) {
        if (is_array($args)) {
            for($n = 1; $n < count($args); $n++) {
                if ((!is_numeric($args[$n])) || (is_a($args[$n],'sqlstr'))) {
                    console::debugEx(LOG_DEBUG2,__CLASS__,"Escaping value: %s", $args[$n]);
                    $args[$n] = $this->conn->quote((string)$args[$n]);
                    console::debugEx(LOG_DEBUG2,__CLASS__,"             -> %s", $args[$n]);
                }

            }
            if (count($args) > 1) {
                $str = call_user_func_array("sprintf",$args);
            } else {
                $str = $args[0];
            }
        } else {
            $str = $this->conn->quote($args);
        }
        return $str;
    }

    function execute($sql) {
        Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Exec: %s", $sql);
        $query = $this->conn->exec($sql);
    }

    function query($sql) {
        Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Query: %s", $sql);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $qt = new Timer(true);

        $query = $this->conn->query($sql);
        $qtt = $qt->stop();
        if (class_exists('OptimizationReport') && ($qtt>=config::get(RuntimeOptimization::KEY_DBQUERYTIME))) {
            $msg = sprintf('<p>The following query took %5.1fs to complete:</p><pre>%s</pre>',$qtt,wordwrap($sql));
            if (class_exists('OptimizationReport')) OptimizationReport::addOptimizationHint('Slow SQL Query', 'DB:00001', 'warning', $msg);
        }
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
    
    function transactionBegin() { }
    function transactionEnd() { }

}


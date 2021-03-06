<?php module("PDO Databaase Driver");

class PdoDatabaseDriver extends DatabaseDriver {

    private $conn;

    private $driver;
    private $host;
    private $db;
    private $user;
    private $pass;
    private $dsn;
    public $autonumber;

    static function parseConnectionString($connstr) {
        // Example connection strings:
        // mysql: user:pass@host/database
        // sqlite: filemname.db
        $c = array();
        $tokens = explode(':', $connstr);
        $connectioninfo = $tokens[3];
        $c['driver'] = $tokens[2];
        switch($tokens[2]) {
            case 'mysql':
                $c['database'] = $tokens[6];
                $c['username'] = $tokens[4];
                $c['password'] = $tokens[5];
                if (substr(0,1,$tokens[7]) == '$') {
                    $c['socket'] = substr($tokens[7],1);
                } else {
                    $c['hostname'] = $tokens[7];
                }
                break;
            case 'sqlite':
                $c['filename'] =  $tokens[3];
                break;
        }
        return $c;
    }

    function __construct($cfg) {
        if (is_array($cfg)) {
            $driver = explode('/',$cfg['driver']);
            $drv = $driver[1];
        } else {
            $cfg =  self::parseConnectionString($cfg);
            $drv = $cfg['driver'];
        }

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
            if (isset($this->db) && ($this->db['database'] != '')) $this->exec("USE ".$this->db);
            $cs = config::get('lepton.charset');
            $cs = str_replace('utf-','utf',$cs); // 'utf8';
            // $this->exec("CHARSET ".$cs);
            if ($this->driver == 'mysql') {
                $this->exec("SET NAMES '".$cs."'");
                $this->exec("SET character_set_results='".$cs."'");
            }
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

    function exec($sql) {
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

}


<?php module("PDO Databaase Driver");

using('lepton.ldb.databasedriver');
using('lepton.ldb.sqlschema');

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
        $cs = config::get('lepton.charset');
        $cs = str_replace('utf-','utf',$cs); // 'utf8';

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
                if ($cs) { $this->dsn .= ';charset='.$cs; }
                $this->driver = 'mysql';
                break;
        }
        Console::debugEx(LOG_DEBUG1,__CLASS__,"Connection DSN: %s.", $this->dsn);
        try {
            $this->conn = new PDO($this->dsn,$this->user,$this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $this->exec("CHARSET ".$cs);
            if ($this->driver == 'mysql') {
                if (isset($this->db) && ($this->db['database'] != '')) $this->execute("USE ".$this->db);
                $this->execute("SET NAMES '".$cs."'");
                $this->execute("SET CHARACTER SET '".$cs."'");
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

    function execute($sql,$attr=null) {
        Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Exec: %s", $sql);
        try {
            $query = $this->conn->exec($sql);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(),intval($e->getCode()),$e);
        }
    }

    function query($sql,$attr=null) {
        Console::debugEx(LOG_DEBUG2,__CLASS__,"SQL Query: %s", $sql);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $qt = new Timer(true);
        try {
            $query = $this->conn->query($sql);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(),intval($e->getCode()),$e);
        }
        $qtt = $qt->stop();
        if (class_exists('OptimizationReport') && ($qtt>=config::get(RuntimeOptimization::KEY_DBQUERYTIME))) {
            $msg = sprintf('<p>The following query took %5.1fs to complete:</p><pre>%s</pre>',$qtt,wordwrap($sql));
            if (class_exists('OptimizationReport')) OptimizationReport::addOptimizationHint('Slow SQL Query', 'DB:00001', 'warning', $msg);
        }
        if ($query) {
            if ($query->rowCount() > 0) {
                try {
                    $fetchmode = MYSQLI_BOTH;
                    if ( $attr & QueryAttributes::QATTR_GET_ASSOC ) {
                        $data = $query->fetchAll(MYSQLI_ASSOC);
                    } elseif ( $attr & QueryAttributes::QATTR_GET_NUMERIC ) {
                        $data = $query->fetchAll(MYSQLI_NUM);
                    } else {
                        $data = $query->fetchAll();
                    }
                    $ret = array(
                        'data' => $data,
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

    function getSchemaManager() {
        switch($this->driver) {
            case 'mysql':
                return new MysqlSchemaManager(new DatabaseConnection($this));
                break;
            case 'sqlite':
                return new SqliteSchemaManager(new DatabaseConnection($this));
                break;
        }
    }

}

class MysqlSchemaManager extends SqlTableSchemaManager { 
    function schemaExists($tablename) {

        $res = $this->conn->getSingleRow("SELECT DATABASE()");
        $database = $res[0];
        $res = $this->conn->getSingleRow(
            'SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema=%s AND table_name=%s',
            $database, $tablename
        );
        return ($res[0] == 1);

    }
    function apply(SqlTableSchema $schema) {
        $sdef = $schema->getDefinition();
        $tname = $sdef['name'];
        $tdrop = $sdef['drop'];
        $tcols = $sdef['columns'];
        $tkeys = $sdef['keys'];
        // Create table
        $sql = 'CREATE TABLE '.$tname." (";
        foreach($tcols as $col) {
            $sql.= $col['name'].' '.$this->getColType($col['type'],$col['opts']);
            if ($col['default']!=null) $sql.= 'DEFAULT '.$col['default'];
            if ($col != end($tcols)) $sql.= ', ';
        }
        foreach($tkeys as $key) {
            switch($key['type']) {
                case SqlTableSchema::KEY_PRIMARY:
                    $keystr = ', PRIMARY KEY '; break;
                case SqlTableSchema::KEY_UNIQUE:
                    $keystr = ', UNIQUE KEY '; break;
                case SqlTableSchema::KEY_FULLTEXT:
                    $keystr = ', FULLTEXT INDEX '; break;
                case SqlTableSchema::KEY_INDEX:
                    $keystr = ', KEY '; break;
            }
            $keystr.=sprintf('%s(%s)',$key['name'],join(',',$key['cols']));
            $sql.=$keystr;			
        }
        
        $sql.= ')';
        if ($tdrop) $this->conn->exec('DROP TABLE IF EXISTS '.$tname);
        $this->conn->exec($sql);
    }
    private function getColType($type,$flags) {
        list($type,$constrain) = explode(':',$type.':');
        switch(strtolower($type)) {
            case 'int':
                if ($constrain) {
                    $otype = sprintf('INT(%d)',$constrain);
                } else {
                    $otype = 'int';
                }
                if ($flags & SqlTableSchema::COL_AUTO) {
                    $otype.= ' AUTO_INCREMENT';
                }
                break;
            case 'char':
            case 'varchar':
            case 'text':
                if ($constrain) {
                    if ($flags & SqlTableSchema::COL_FIXED) {
                        $otype = sprintf('CHAR(%d)',$constrain);
                    } else {
                        $otype = sprintf('VARCHAR(%d)',$constrain);
                    }
                } else {
                    if ($flags & SqlTableSchema::COL_BINARY) {
                        $otype = sprintf('BLOB');
                    } else {
                        $otype = sprintf('TEXT');
                    }
                }
                break;
            case 'enum':
                $cl = explode(',',$constrain);
                $cls = '';
                foreach($cl as $c) {
                    if (strlen($cls)>0) $cls.=',';
                    $cls.="'".$c."'";
                }
                $otype = 'ENUM('.$cls.')';
                break;
            default:
                logger::warning("Unhandled field type: %s", $type);
                $otype = '';
        }
        // Check nullable state
        if ($flags & SqlTableSchema::COL_NULLABLE) {
            $otype.= ' NULL';
        } else {
            $otype.= ' NOT NULL';
        }
        // Keys
        if ($flags & SqlTableSchema::KEY_PRIMARY) {
            $otype.= ' PRIMARY KEY';
        }
        if ($flags & SqlTableSchema::KEY_UNIQUE) {
            $otype.= ' UNIQUE KEY';
        }
        return $otype;
    }
}
class SqliteSchemaManager extends SqlTableSchemaManager {
    function apply(SqlTableSchema $schema) { }
}


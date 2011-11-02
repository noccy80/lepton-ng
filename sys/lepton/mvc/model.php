<?php

    class ModelException extends BaseException { }
    class BadDefinitionException extends ModelException { }

    abstract class Model {
        function find($by,$value) {

        }
    }

    abstract class ActiveModel extends Model {

    }

    abstract class AbstractModel extends Model {

        protected $_fields;
        protected $_data;
        protected $_index;
        protected $_set;

        public function __construct($initial=null) {
            if (!isset($this->model)) {
                Console::warn('Bad model doesn\'t contain $model variable');
            }
            if (isset($this->fields)) {
                foreach($this->fields as $field=>$meta) {
                    if (!$this->addField($field,$meta)) {
                        Console::warn('Failed to define field %s as %s', $field, $meta);
                    }
                }
            }
            $this->clear();
            if ($initial) {
                foreach($this->_fields as $field=>$meta) {
                    if (($meta['required']) && (!isset($initial[$field]))) {
                        Console::warn('Initializin withoutg required data (%s)', $field);
                    }
                }
                foreach($initial as $field=>$data) { $this->_data[$field] = $data; }
            }
        }

        protected function addField($field,$meta) {
            // TODO: Verify the meta format
            $md = explode(' ',$meta); $mi = 0;
            $mo = array();
            // Console::debugEx(LOG_DEBUG2,__CLASS__,"Parsing quotes in array for %s", $meta);
            // Console::debugEx(LOG_DEBUG2,__CLASS__," \$md = {'%s'}", join("','", $md));
            while($mi < count($md)) {
                // Console::debugEx(LOG_DEBUG2,__CLASS__,"Current token: %s", $md[$mi]);
                if ($md[$mi][0] == '"') {
                    $buf = array();
                    while($mi < count($md)) {
                        $str = $md[$mi];
                        $buf[] = $md[$mi++];
                        // Console::debugEx(LOG_DEBUG2,__CLASS__," -- Quoted token: %s (%s)", $str, $str[strlen($str)-1]);
                        if ($str[strlen($str)-2] == '"') break;
                    }
                    $bufstr = join(' ',$buf);
                    $bufstr = substr($bufstr,1,strlen($bufstr)-2);
                    $mo[] = $bufstr;
                    Console::debugEx(LOG_DEBUG2,__CLASS__,"Joined quoted statement: %s", $bufstr);
                } else {
                    $mo[] = $md[$mi++];
                }
            }
            $md = $mo;
            // Console::debugEx(LOG_DEBUG2,__CLASS__," \$md = {'%s'}", join("','", $md));
            $ftype = null; $fdef = null; $freq = false; $fprot = false;
            $mi = 0;
            while($mi < count($md)) {
                // Console::debugEx(LOG_DEBUG1,__CLASS__,'Parsing abstract model field %s: %s', $field, $md[$mi]);
                switch(strtolower($md[$mi])) {
                    case 'string':
                        $ftype = 'STRING';
                        break;
                    case 'int':
                        $ftype = 'INT';
                        break;
                    case 'bool':
                        $ftype = 'BOOL';
                        break;
                    case 'set':
                        $ftype = 'SET';
                        break;
                    case 'enum':
                        $ftype = 'STRING';
                        break;
                    case 'required':
                        $freq = true;
                        break;
                    case 'protected':
                        $fprot = true;
                        break;
                    case 'index':
                        $this->_index = $field;
                        break;
                    case 'default':
                        $fdef = $md[++$mi];
                        break;
                    case 'like':
                        $flike = $md[++$mi];
                        break;
                    case 'in':
                    case 'of':
                        $fin = $md[++$mi];
                        break;
                    case 'format':
                        if (($ftype == 'INT') || ($ftype == 'STRING')) {
                            // Check format
                        } else {
                            Console::warn('Format declaration for key %s ignored', $field);
                        }
                        break;
                    case 'auto':
                        if (($ftype == 'INT')) {

                        } else {
                            Console::warn('Only INT can be auto fields');
                        }
                        $fauto = true;
                        break;
                }
                $mi++;
            }
            if (($ftype != null)) {
                $this->_fields[$field] = array(
                    'type' => $ftype,
                    'required' => $freq,
                    'default' => $fdef,
                    'protected' => $fprot
                );
                return true;
            } else {
                Console::warn('Bad type specified for field %s in AbstractModel implementation', $field);
                Console::backtrace();
            }
            return false;
        }

        public function clear() {
            foreach($this->_fields as $field=>$meta) {
                $this->_data[$field] = $meta['default'];
            }
        }

        public function inspect() {
            Console::writeLn('Inspecting model %s:', $this->model);
            foreach($this->_data as $field=>$data) {
                Console::writeLn('  %s = %s', $field, $data);
            }
        }

        public function saveTo(AbstractStreamIoWriter $writer) {

        }

        public function load(AbstractStreamIoReader $reader) {
            if ($reader->getDefinition()) {
            }
            while ($row = $reader->getRow()) {
                $this->_set[] = $row;
            }
        }

        public function __get($field) {
            if (isset($this->_fields[$field])) {
                return ($this->_data[$field]);
            } else {
                throw new Exception("No such field in model");
            }
        }
        public function __set($field,$value) {
            if (isset($this->_fields[$field])) {
                $this->_data[$field] = $value;
                console::debugEx(LOG_DEBUG,__CLASS__,"Setting %s.%s to %s", $this->model, $field, $value);
            } else {
                throw new Exception("No such field in model");
            }
        }

    }

////////////// SAVING AND LOADING OF DATA /////////////////////////////////////

    /**
     * @interface IAbstractStreamIoReader
     * @brief Interface for the AbstractStreamIoReader
     *
     * Defines the methods that need to be exposed by all readers
     */
    interface IAbstractStreamIoReader {
        /**
         * Reads a definition set from the file, the result should be an array
         * defining the number of fields present and some metadata.
         */
        function readDefinition();
        function readRecord();
    }
    
    interface IAbstractStreamIoWriter {
        function write($file);
    }

    interface IAbstractStreamBase {
        function open($file=null);
    }

    abstract class AbstractStreamIoBase implements IAbstractStreamBase {
        protected $_filename;
        function __construct($filename=null) {
            $this->_filename = $filename;
            $this->open($this->_filename);
        }
        protected function getLock() {
            
        }
        protected function releaseLock() {

        }
        function isOpen() {

        }
        function isEof() {

        }
    }

    abstract class AbstractStreamIoReader extends AbstractStreamIoBase implements IAbstractStreamIoReader {
        public $filename;
    }

    abstract class AbstractStreamIoWriter extends AbstractStreamIoBase implements IAbstractStreamIoWriter {

    }

    class CsvAsiReader extends AbstractStreamIoReader {
        private $fh;
        function __construct($filename) {
            parent::__construct($filename);
        }
        function open($filename = null) {
            if ($filename) {
                // Load CSV
                $this->fh = fopen($filename,'r');
            }
        }
        function readRecord() {
            $row = fgetcsv($this->fh, 1000, "\t","\"","\\");
            return $row;
        }
        function readDefinition() {
            return null;
        }
    }

    class XmlAsiReader extends AbstractStreamIoReader {
        private $fh;
        function open($filename = null) {
            if ($filename) {
                // Load XML
            }
        }
        function readRecord() {

        }
        function readDefinition() {
            
        }
    }

    class BinaryAsiReader extends AbstractStreamIoReader {
        function open($filename = null) {

        }
        function readRecord() {

        }
        function readDefinition() {

        }
    }

//////////////////////////// TESTING CODE /////////////////////////////////////

    class ForumsRecord extends ActiveModel {
        var $table = "forums";
        var $fields = array(
            'id'         => 'int auto index',
            'parent'     => 'int required default 0',
            'slug'       => 'string required format 64',
            'name'       => 'string required format 64',
            'descripion' => 'string',
            'posts'      => 'int required default 0',
            'threads'    => 'int required default 0',
            'flags'      => 'set of "public,private,hidden,locked" default "public"'
        );
    }

    class TestModel extends AbstractModel {
        var $model = 'TestModel';
        var $fields = array(
            'name'      => 'string required default "untitled user"',
            'age'       => 'int default 0',
            'active'    => 'bool default false'
        );
    }

    $tm = new TestModel(array(
        'age'           => 30,
        'name'          => 'bob'
    ));

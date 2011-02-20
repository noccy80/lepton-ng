<?php

interface IDataSet extends IteratorAggregate {
    function getSetName();
    function getSetData();
}

class DataSet implements IDataSet {

    private $_name = null;
    private $_data = null;

    function __construct($name=null,$data=null) {
        $args = func_get_args();
        $this->_name = (count($args) > 0)?$args[0]:null;
        $this->_data = (array)(count($args)>1)?array_slice($args,1):array();
    }

    function getSetName() {
        return $this->_name;
    }

    function getSetData() {
        return $this->_data;
    }

    function getIterator() {
        return $this->_data;
    }

}

$ds = new DataSet("Apples",0,1,2,3,4);

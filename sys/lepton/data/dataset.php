<?php

interface IDataSet {
    function getSetName();
    function getSetData();
}

class DataSet implements IDataSet {

    private $_label = null;
    private $_data = null;

    function __construct($label=null,$data=null) {
        $args = func_get_args();
        $this->_label = (count($args) > 0)?$args[0]:null;
        $this->_data = (array)(count($args)>1)?array_slice($args,1):array();
    }

    function getSetLabel() {
        return $this->_label;
    }

    function getSetData() {
        return $this->_data;
    }

}


$ds = new DataSet("Apples",0,1,2,3,4);

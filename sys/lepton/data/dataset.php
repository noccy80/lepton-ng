<?php

interface IDataSet {
    function getLabel();
    function getData();
}

class DataSet implements IDataSet {

    private $_label = null;
    private $_data = null;

    function __construct($data=null) {
        $args = func_get_args();
        $this->_data = $args;
    }

    function getLabel() {
        return $this->_label;
    }

    function setLabel($label) {
    	$this->_label = $label;
    }

    function getData() {
        return $this->_data;
    }

}


$ds = new DataSet(0,1,2,3,4);
$ds->setLabel("Apples");

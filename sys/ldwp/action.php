<?php

interface ILdwpAction {
    function process(WorkerState $worker, ActionState $action);

}

abstract class LdwpAction implements ILdwpAction {




}

class WorkerState {

    private $statedata = array();

    public function __get($name) {
        if (arr::hasKey($this->statedata, $name)) {
            return $this->statedata[$name];
        } else {
            return NULL;
        }
    }

    public function __set($name,$value) {
        if ($value == NULL) {
            unset($this->statedata[$name]);
        } else {
            $this->statedata[$name] = $value;
        }
    }

}

class ActionState {

    private $statedata = array();

    public function __get($name) {
        if (arr::hasKey($this->statedata, $name)) {
            return $this->statedata[$name];
        } else {
            return NULL;
        }
    }

    public function __set($name,$value) {
        if ($value == NULL) {
            unset($this->statedata[$name]);
        } else {
            $this->statedata[$name] = $value;
        }
    }

}
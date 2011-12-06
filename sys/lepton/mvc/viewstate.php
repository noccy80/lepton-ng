<?php

/**
 * @class ViewState
 * @brief Stores (semi-)persistent data in a request.
 *
 * This class can be used to store data accessible to the router and controller
 * as well as the view. 
 *
 * @author Christopher Vagnetoft <noccy.com>
 */
class ViewState {

    private $stateid = null;
    private $state = array();
    private static $pstate = array();

    /**
     * @brief Constructor
     *
     * @param Mixed $stateid The state ID to access
     * @throw BaseException
     */
    function __construct($stateid=null) {
        if ($stateid == null) {
            $this->state = self::$pstate;
        } else {
            if (session::has('viewstate_'.$stateid)) {
                $this->state = session::get('viewstate_'.$stateid);
            } else {
                throw new BaseException("No viewstate found with id");
            }
        }
    }

    /**
     * @brief Get a value from the viewstate (property)
     *
     * @param String $key The key to query
     */
    function __get($key) {
        return $this->get($key);
    }

    /**
     * @brief Get a value from the viewstate
     *
     * @param String $key The key to query
     * @param Mixed $default The default value if the key is not set
     */
    function get($key,$default=null) {
        if (isset($this->state[$key])) return $this->state[$key];
        return $default;
    }

    /**
     * @brief Set a value in the viewstate (property)
     *
     * @param String $key The key to set
     * @param Mixed $value The new value
     */
    function __set($key,$value) {
        $this->set($key,$value);
    }

    /**
     * @brief Set a value in the viewstate
     *
     * @param String $key The key to set
     * @param Mixed $value The new value
     */
    function set($key,$value) {
        $this->state[$key] = $value;
        if ($this->stateid==null) self::$pstate[$key]=$value;
    }
    
    /**
     * @brief Push a value onto a key, making it an array.
     * 
     * @param String $key The key to push to
     * @param Mixed $value The value to push onto the key
     */
    function push($key,$value) {
        if (arr::hasKey($this->state,$key)) {
            $arr = (array)$this->state[$key];
        } else {
            $arr = array();
        }
        $arr[] = $value;
        $this->set($key,$arr);
    }

    /**
     * @brief Save the viewstate and return the assigned id.
     *
     * This ID can be used with the constructor to re-open the specified
     * viewstate
     *
     * @return String The assigned ID
     */
    function save() {
        $this->stateid = 'viewstate_'.uniqid();
        session::set('viewstate_'.$this->stateid,$this->state);
        return $this->stateid;
    }
    
    /**
     * @brief Delete the current viewstate.
     *
     * The data will remain in place in the object, so save() can be called
     * again to create a new state.
     */
    function delete() {
        if ($this->stateid!=null) {
            session::clr('viewstate_'.$this->stateid);
            $this->stateid = null;
        } else {
            throw new BaseException("Can't delete a non-saved viewstate");
        }
    }

    /**
     * @brief Update the current viewstate.
     *
     */
    function update() {
        if ($this->stateid!=null) {
            session::set('viewstate_'.$this->stateid,$this->state);
        } else {
            throw new BaseException("Can't update a non-saved viewstate");
        }
    }

}

function viewstate($id=null) {
    return new ViewState($id);
}

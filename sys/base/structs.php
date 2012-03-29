<?php

////// Structures /////////////////////////////////////////////////////////////

/**
 * @brief Contains a handy list object.
 * 
 * When constructed with an argument, the type will be "locked" to the type
 * specified to the constructor. So, in order to create a list of only Widgets,
 * you can just pass "widget" to the constructor.
 * 
 * It supports all the modern facilities such as push, pop, array access, and
 * can also be iterated.
 * 
 * @author Christopher Vagnetoft
 */
class BasicList implements IteratorAggregate, ArrayAccess {

    private $list;
    private $type = null;

    /**
     * @brief Constructor, assigns type constraint if any
     * 
     * @param string $typeconst The type constraint to use
     */
    public function __construct($typeconst=null) {
        $this->type = $typeconst;
    }

    /**
     * @brief Returns an iterator (for the foreach operator)
     * 
     * @return ArrayIterator The iterator to go over the list
     */
    public function getIterator() {
        return new ArrayIterator((array) $this->list);
    }

    /**
     * @brief Adds an item, alias of push()
     * 
     * @param Mixed $item Add the specific item to the list
     */
    public function add($item) {
        $this->push($item);
    }

    /**
     * @brief Adds an item to the list.
     *
     * @param Mixed $item Add the specific item to the list
     */
    public function push($item) {
        if ($this->type) {
            if (!is_a($item, $this->type)) {
                throw new BaseException("Error; Pushing invalid type with add(). " . $item . " is not a " . $this->type);
            }
        }
        $this->list[] = $item;
    }
    
    /**
     * @brief Pops the last item off the list.
     * 
     * The item is removed from the list after popping it.
     * 
     * @return Mixed The last item
     */
    public function pop() {
        if (count($this->list) > 0) {
            $item = array_pop($this->list);
            return $item;
        }
        return null;
    }

    /**
     * @brief Return the item with the specified index.
     * 
     * @param Int $index The index to query.
     * @return Mixed The item.
     */
    public function item($index) {
        return $this->list[$index];
    }

    /**
     * @brief Return true if the item is found in the list.
     * 
     * @param Mixed $item The item to match
     * @return Bool True if the item was found.
     */
    public function find($item) {
        return (in_array($item, $this->list));
    }

    /**
     *
     * @return type 
     */
    public function count() {
        return count($this->list);
    }
    
    /**
     * 
     */
    public function sort() {
        sort($this->list);
    }
    
    /**
     *
     * @param type $offset
     * @return type 
     */
    public function offsetExists($offset) {
        return (arr::hasKey($this->list,$offset));
    }
    
    /**
     *
     * @param type $offset
     * @return type 
     */
    public function offsetGet($offset) {
        return ($this->list[$offset]);
    }
    
    /**
     *
     * @param type $offset
     * @param type $item 
     */
    public function offsetSet($offset, $item) {
        if ($this->type) {
            if (!is_a($item, $this->type)) {
                throw new BaseException("Error; Pushing invalid type with add(). " . $item . " is not a " . $this->type);
            }
        }
        $this->list[$offset] = $item;
    }
    
    /**
     *
     * @param type $offset 
     */
    public function offsetUnset($offset) {
        unset($this->list[$offset]);
    }

}

class BasicContainer {

    protected $propertyvalues = array();

    function __construct() {
        if (!isset($this->properties)) {
            throw new RuntimeException("BasicContainer descendant doesn't have a protected variable properties");
        }
    }

    function __get($property) {
        if (!isset($this->properties)) {
            throw new RuntimeException("BasicContainer descendant doesn't have a protected variable properties");
        }
        if (arr::hasKey($this->properties,$property)) {
            return $this->properties[$property];
        } else {
            throw new BadPropertyException("No such property: $property");
        }
    }

    function __set($property, $value) {
        if (!isset($this->properties)) {
            throw new RuntimeException("BasicContainer descendant doesn't have a protected variable properties");
        }
        if (arr::hasKey($this->properties,$property)) {
            if (is_array($this->properties[$property]) &&
                (!is_array($value))) {
                throw new RuntimeException("Attempting to assign non-array to array property");
            }
            $this->properties[$property] = $value;
        } else {
            throw new BadPropertyException("No such property: $property");
        }
    }

    function __isset($property) {
        if (!isset($this->properties)) {
            throw new RuntimeException("BasicContainer descendant doesn't have a protected variable properties");
        }
        return (isset($this->properties[$property]));
    }
    
    function getData() {
        return $this->propertyvalues;
    }
    
    function setData($data) {
        arr::apply($this->propertyvalues, $data);
    }

}


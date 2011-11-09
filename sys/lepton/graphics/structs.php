<?php

/**
 * @class Point
 * @brief Contains a point with x and y coordinates.
 *
 * @see point()
 */
class Point {

    private $x;
    private $y;

    /**
     *
     * @param Integer $x
     * @param Integer $y
     */
    function __construct($x,$y) { 
        $this->x = intval($x); $this->y = intval($y); 
    }

    /**
     *
     */
    function __get($key) {
        if (isset($this->{$key})) return $this->{$key};
        throw new BadPropertyException($key);
    }

    /**
     *
     */
    function getPoint() { 
        return array($this->x, $this->y); 
    }
}

/**
 * @class Rect
 * @brief Contains a rectangle consisting of x and y
 *
 * @see rect()
 */
class Rect {

    private $x;
    private $y;
    private $w;
    private $h;

    /**
     * @brief Helper function to create a rectangle from two points.
     *
     * @param Point $topleft
     * @param Point $bottomright
     * @return Rect The rectangle
     */
    static function createFromPoints(Point $topleft, Point $bottomright) {
        return new Rect($topleft->x, $topleft->y, $bottomright->x - $topleft->x, $bottomright->y - $topleft->y);
    }

    /**
     *
     * @param Integer $x
     * @param Integer $y
     * @param Integer $w
     * @param Integer $h
     */
    function __construct($x,$y,$w,$h) { 
        $this->x = intval($x); $this->y = intval($y); 
        $this->w = intval($w); $this->h = intval($h); 
    }

    /**
     *
     */
    function __get($key) {
        if (isset($this->{$key})) return $this->{$key};
        throw new BadPropertyException($key);
    }

    /**
     *
     */
    function getRect() { 
        return array($this->x, $this->y, $this->w, $this->h); 
    }
}

/**
 *
 * @param Integer $x
 * @param Integer $y
 */
function point($x,$y) {
    return new Point($x,$y);
}

/**
 *
 * @param Integer $x
 * @param Integer $y
 * @param Integer $w
 * @param Integer $h
 */
function rect($x,$y,$w,$h) {
    return new Rect($x,$y,$w,$h);
}

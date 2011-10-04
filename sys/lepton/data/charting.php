<?php

using('lepton.graphics.canvas');
using('lepton.graphics.colorspaces.*');

/**
 * @interface IChart
 * @brief Defines the interface for charting components.
 * 
 * This is used by the Chart and GChart abstract classes to provide some common
 * ground for rendering and manipulating charts based on the Lepton DataSet and
 * DataSeries classes.
 * 
 * @author Christopher Vagnetoft
 */
interface IChart { 
	public function __construct($width,$height);
	// Render and return canvas
	function render();
}

/**
 * @class Chart
 * @brief Base class for charts and renderers.
 * 
 * @abstract
 */
abstract class Chart implements IChart {

	protected $width = null;
	protected $height = null;
	protected $dataset = null;
	protected $props = array();
	protected $ovlobjects = array();

    /**
     * @brief Retrieve a defined property
     * 
     * @param String $key The key to query
     * @param Mixed $default The default value (null)
     * @return Mixed The property value
     */
	protected function getProperty($key,$default=null) {
		if (isset($this->props[$key]))
			return ($this->props[$key]);
		return $default;
	}
	
    /**
     * @brief Assigns a property
     * 
     * @param String $key The key to assign
     * @param Mixed $value The property value
     */
	protected function setProperty($key,$value) {
		$this->props[$key] = $value;
	}

    /**
     * @brief Assign multiple properties at once
     * 
     * @param Array $data An associative array holding the keys and values
     */
	protected function setProperties(Array $data) {
		foreach($data as $key=>$value)
			$this->props[$key] = $value;
	}

    /**
     * @brief Constructor
     * 
     * @param Int $width The width of the charting area
     * @param Int $height The height of the charting area
     */
	public function __construct($width,$height) {
		$this->width = $width;
		$this->height = $height;
	}
	
    /**
     * @brief Assign the data set
     * 
     * @param DataSet $data The data to assign
     */
	public function setData(DataSet $data) {
		$this->dataset = $data;
	}
	
    /**
     * @brief Assign a property value
     * 
     * @param String $key The key to assign
     * @param Mixed $value The value to assign
     */
	public function __set($key,$value) {
		$this->props[$key] = $value;
	}
	
    /**
     * @brief Get a property value
     * 
     * @param String $key The key to query
     * @return Mixed The property value or null if not set
     */
	public function __get($key) {
		if (isset($this->props[$key]))
			return ($this->props[$key]);
		return null;
	}
	
    /**
     * @brief Add an overlayed object to the chart
     * 
     * @param Drawable $object The drawable to place on the chart
     * @param Rect $placement The placement of the drawable
     */
	public function addObject(Drawable $object, Rect $placement) {
		$this->ovlobjects[] = array(
			'object' => $object,
			'placement' => $placement
		);
	}
	
    /**
     * @brief Render overlayed objects onto the chart
     * 
     * @todo The objects should have full access to the chart data.
     * @param Canvas $c The canvas to draw onto
     * @protected
     */
	protected function renderObjects(Canvas $c) {
		foreach($this->ovlobjects as $object) {
			list($x,$y,$w,$h) = $object['placement']->getRect();
            $object['object']->setData($this->dataset);
			$object['object']->draw($c,$x,$y,$w,$h);
		}
	}

}

class ChartAxis {
    
    private $xmin = null;
    private $ymin = null;
    private $xmax = null;
    private $ymax = null;
    
    public function __construct($xmin=null,$ymin=null,$xmax=null,$ymax=null) {
        
    }
    
    public function adjust(DataSet $data) {
        
        // Go over the data and adjust the series as needed
        
    }
    
}

function chartaxis($xmin=null,$ymin=null,$xmax=null,$ymax=null) {
    return new ChartAxis($xmin,$ymin,$xmax,$ymax);
}
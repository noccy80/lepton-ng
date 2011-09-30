<?php module("Google Charts API");

// Passthrough requests when possible (redirect instead of proxying)
config::def('google.charts.api.passthrough', true);

// Load the data storage that we need
using('lepton.data.*');

interface IGChart {
	function buildPostData();
}
/**
 * @class GChart
 * @brief Google Charting Class.
 * Draws charts via Google's Chart API either by redirecting or proxying the
 * request.
 *
 * @property width The width of the graph
 * @property height The height of the graph
 * @package lepton.google.charting
 */
abstract class GChart extends Chart implements IGChart, IChart {

    /// @const Configuration key for if charts are to be passed through
	const CONF_PASSTHROUGH = 'google.charts.api.passthrough';
    /// @var The pool to use for distributing the load over several servers
	static $spool = 0; 
	private $charttype = 'p3';
	protected $width;
	protected $height;

    /**
     *
     * @param type $width
     * @param type $height 
     */
	function __construct($width, $height) {
        parent::__construct($width,$height);
	}
	
    /**
     * @brief Return a pooled URL to Googles chart API
     * 
     * @param type $args
     * @return string 
     * @protected
     */
	protected function getPooledUrl($args) {
		$spool = ($spool++ % 10);
		$url = 'http://'.$spool.'.chart.apis.google.com/chart';
		$urlqs = array();
		foreach($args as $key=>$val) {
			$urlqs[] = $key.'='.urlencode($val);
		}
		$url = $url . '?'. join('&', $urlqs);
		return $url;
	}	

	function __set($key,$value) {
		switch($key) {
		case 'width':
			$this->width = $value;
			break;
		case 'height':
			$this->height = $value;
			break;
		default:
            parent::__set($key,$value);
            break;
		}
	}
	
	function render() {
		$chart = $this->buildPostData();
		$chart['chd'] = 't:60,40';
		$chart['chl'] = 'Hello|World';
		if (config::get(GChart::CONF_PASSTHROUGH) == true) {
			$this->doRenderPost($chart);
		} else {
			$this->doRenderGet($chart);
		}
	}
	
	function doRenderPost($chart) {
		// Create some random text-encoded data for a line chart.
		header('content-type: image/png');
		$url = 'http://chart.apis.google.com/chart?chid=' . md5(uniqid(rand(), true));

        // Send the request, and print out the returned bytes.
		$context = stream_context_create(
			array('http' => array(
				'method' => 'POST',
				'header' => 'content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($chart))
			));
		fpassthru(fopen($url, 'r', false, $context));
	}
	
	function doRenderGet($chart) {
		$url = $this->getPooledUrl($chart);
		response::redirect($url);
	}

}

/**
 * @class GBarChart
 * @brief Bar Chart via Google's Charting API
 */
class GBarChart extends GChart {
	public function buildPostData() {
		$pd = array(
			'chs' => $this->width.'x'.$this->height,
			'cht' => $this->charttype
		);
		return $pd;
	}
}

/**
 * @class GPieChart
 * @brief Pie Chart via Google's Charting API
 */
class GPieChart extends GChart {
	public function buildPostData() {
		$pd = array(
			'chs' => $this->width.'x'.$this->height,
			'cht' => $this->charttype
		);
		return $pd;
    }
}

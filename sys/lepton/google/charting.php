<?php __fileinfo("Google Charts API");

// Passthrough requests when possible (redirect instead of proxying)
config::def('google.charts.api.passthrough', true);

// Load the data storage that we need
modulemanager::load('lepton.data.*');

/**
 * @class GChart
 * @brief Google Charting Class.
 * Draws charts via Google's Chart API either by redirecting or proxying the request.
 */
class GChart {

	const CONF_PASSTHROUGH = 'google.charts.api.passthrough';
	static $spool = 0; // Server pool
	private $charttype = 'p3';
	private $width;
	private $height;
	
	function getPooledUrl($args) {
		$spool = ($spool++ % 10);
		$url = 'http://'.$spool.'.chart.apis.google.com/chart';
		$urlqs = array();
		foreach($args as $key=>$val) {
			$urlqs[] = $key.'='.urlencode($val);
		}
		$url = $url . '?'. join('&', $urlqs);
		return $url;
	}	

	function __construct(DataSet $data, $width, $height) {
		$this->width = $width;
		$this->height = $height;
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
			throw new BaseException("No such property");
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
	
	function buildPostData() {
		$pd = array(
			'chs' => $this->width.'x'.$this->height,
			'cht' => $this->charttype
		);
		return $pd;
	}

	function doRenderPost($chart) {
		// Create some random text-encoded data for a line chart.
		header('content-type: image/png');
		$url = 'http://chart.apis.google.com/chart?chid=' . md5(uniqid(rand(), true));
		/*
		$chd = 't:';
		for ($i = 0; $i < 150; ++$i) {
			$data = rand(0, 100000);
			$chd .= $data . ',';
		}
		$chd = substr($chd, 0, -1);

		// Add data, chart type, chart size, and scale to params.
		$chart = $this->buildPostData();
		$chart['cht'] = 'lc';
		$chart['chds'] = '0,100000';
		$chart['chd'] = $chd;
		*/
		// var_dump($chart);
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

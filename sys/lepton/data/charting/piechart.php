<?php

using('lepton.data.charting');

class PieChart extends Chart {

	const STYLE_2D = '2d';
	const STYLE_3D = '3d';

	function __construct($width,$height) {
		$this->setProperties(array(
			'legend' => true,
			'style' => PieChart::STYLE_3D
		));
		parent::__construct($width,$height);
	}

	function setData(DataSet $data) {
	
	}

}

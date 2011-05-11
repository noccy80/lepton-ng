<?php

using('lunit.*');

/**
 * @description Data and Charting Tests
 */
class LeptonDataTests extends LunitCase {

	private $ds;

	function __construct() {
		using('lepton.data.*');
		using('lepton.data.charting.*');
	}

	function dataset() {
		$ds = new DataSet('A','B','C','D');
		$this->assertNotNull($ds);
		$ds->addSeries('Sales', new DataSeries(50, 150, 75, 120));
		$ds->addSeries('Services', new DataSeries(50, 55, 60, 65));
		$this->assertEquals($ds->getCount(),2);
		$this->ds = $ds;
	}
	
	function piechart() {
		$pc = new PieChart(400,400);
		$this->assertNotNull($pc);
		$pc->setData($this->ds);
		$c = $pc->render();
		$this->assertEquals($c->width,400);
		$this->assertEquals($c->height,400);
	}	

}

Lunit::register('LeptonDataTests');


<?php

using('lpf.actor');
using('lpf.actors.*');

/**
 * Conway's Game of Life implemented on top of Lepton Presentation Framework.
 *
 * The rules are simple:
 *  - Any live cell with fewer than two live neighbours dies, as if caused by
 *    under-population.
 *  - Any live cell with two or three live neighbours lives on to the next
 *    generation.
 *  - Any live cell with more than three live neighbours dies, as if by over-
 *    crowding.
 *  - Any dead cell with exactly three live neighbours becomes a live cell, as
 *    if by reproduction.
 *
 * @author Christopher Vagnetoft <noccy.com>
 * @license GNU General Public License (GPL) Version 3
 */

class LifeCell_Actor extends LpfActor {

	function create() {
		// Initialize some cells with a life spark.
		$this->addProperty('alive', (mt_rand(0,100)>70) , lpf::bool());
		$this->addProperty('neighbours', 0, lpf::int(0,8));
	}

	function render(SceneState $ss, ActorState $as, Canvas $c) {
		$p = $c->getPainter();
		if ($this->alive) {
			if ($this->neighbours == 0) { $color = rgb(150,80,50); }
			if ($this->neighbours == 1) { $color = rgb(80,80,50); }
			if ($this->neighbours == 2) { $color = rgb(40,150,30); }
			if ($this->neighbours == 3) { $color = rgb(0,200,00); }
			if ($this->neighbours >  3) { $color = rgb(100,80,50); }
		} else {
			$color = rgb(20,30,40);
		}
		$p->drawFilledRect(0,0,$this->width,$this->height,rgb(40,40,40),$color);
	}

}

class LifeGrid_Actor extends LpfActor {

	protected $cells = array();
	protected $stage = array();

	function create() {
		$this->addProperty('rows',255, lpf::int(1,512));
		$this->addProperty('columns',255, lpf::int(1,512));
	}

	function getCellXY($row,$col) {
		if (($row<0) || ($row>$this->rows) || ($col<0) || ($col>$this->columns)) return null;
		$index = ($row * $this->columns) + $col;
		if (count($this->cells)>$index) {
			return $this->cells[$index];
		} else {
			return null;
		}
	}

	function generate() {

		$total = ($this->columns * $this->rows);
		for($a = 0; $a < $this->rows; $a++) {
			$op = false;
			lpf::updateStatus('Staging',$a, $this->rows);
			for($b = 0; $b < $this->columns; $b++) {
				$index = ($a*$this->columns) + $b;
				if ($index>=count($this->cells)) {
					$op = true;
					// Spawn a random cell
					$this->cells[$index] = new LifeCell_Actor();
					$this->stage[$index] = new LifeCell_Actor();
				}
			}
			if ($op) lpf::doTick();

		}

		$algo = 'noccy';
		for($a = 0; $a < $this->rows; $a++) {
			lpf::doTick();
			lpf::updateStatus('Breeding',$a, $this->rows);
			for($b = 0; $b < $this->columns; $b++) {
				$index = ($a*$this->columns) + $b;
				// Create the next mutation
				$na = 0;
				if ($this->isAlive($b - 1, $a - 1)) $na++;
				if ($this->isAlive($b - 1, $a    )) $na++;
				if ($this->isAlive($b - 1, $a + 1)) $na++;
				if ($this->isAlive($b    , $a - 1)) $na++;
				if ($this->isAlive($b    , $a + 1)) $na++;
				if ($this->isAlive($b - 1, $a - 1)) $na++;
				if ($this->isAlive($b - 1, $a    )) $na++;
				if ($this->isAlive($b - 1, $a + 1)) $na++;
				$this->stage[$index]->neighbours = $na;

				$alive = $this->isAlive($b, $a);
				$this->setAlive($b, $a, $alive);
				switch($algo) {
				case 'noccy':
					if (($na < 2) && $alive) {
						$this->setAlive($b, $a, false);
					} elseif (($na > 3) && $alive) {
						$this->setAlive($b, $a, false);
					} elseif (($na == 3) && (!$alive)) {
						$this->setAlive($b, $a, true);
					} elseif ((!$alive) && (mt_rand(0,100)>95)) {
						// $this->setAlive($b, $a, true);
					}
					break;
				default:
					if (($na < 2) && $alive) { $alive = false; }
					elseif (($na > 5) && $alive) { $alive = false; }
					elseif (($na == 3) && (!$alive)) { $alive = true; }
				}
			}
		}
		// Flip the stage to the actual display
		$this->cells = $this->stage;

	}

	function isAlive($col, $row) {
		if (($row<=0) || ($row>=$this->rows) || ($col<=0) || ($col>=$this->columns)) return false;
		$index = ($row * $this->columns) + $col;
		if (count($this->cells)>$index) {
			return $this->cells[$index]->alive;
		} else {
			return false;
		}
	}

	function setAlive($col, $row, $alive) {
		if (($row<0) || ($row>$this->rows) || ($col<0) || ($col>$this->columns)) return;
		$index = ($row * $this->columns) + $col;
		if (count($this->stage)>$index) {
			$this->stage[$index]->alive = $alive;
		}
	}

	function render(SceneState $ss, ActorState $as, Canvas $c) {

		$this->generate();

		for($a = 0; $a < $this->rows; $a++) {
			lpf::doTick();
			lpf::updateStatus('Render',$a, $this->rows);
			for($b = 0; $b < $this->columns; $b++) {
				$index = ($a*$this->columns) + $b;
				$w = $this->width/$this->columns;
				$h = $this->height/$this->rows;
				$x = $b * $w;
				$y = $a * $h;
				if ($this->cells[$index]) {
					$this->cells[$index]->moveTo($x, $y, $w*0.9, $h*0.9);
					// Draw the cell
					$rc = new Canvas($w,$h);
					
					$this->cells[$index]->render($ss,$as,$rc);
					$bf = new BitmapFont(1);
					$rc->drawText($bf,rgb(255,255,255),1,1,$this->cells[$index]->neighbours);
					$rc->draw($c, $x, $y, $w*0.9, $h*0.9);
				}
			}
		}

	}

}

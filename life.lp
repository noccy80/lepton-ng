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

	function render(SceneState $ss, ActorState $as, Canvas $c) {

		for($a = 0; $a < $this->rows; $a++) {
			$op = false;
			lpf::updateStatus('Staging',$a, $this->rows);
			for($b = 0; $b < $this->columns; $b++) {
				$index = ($a*$this->columns) + $b;
				if ($index>=count($this->cells)) {
					$op = true;
					// Spawn a random cell
					$this->cells[$index] = new LifeCell_Actor();
					$w = $this->width/$this->columns;
					$h = $this->height/$this->rows;
					$x = $b * $w;
					$y = $a * $h;
					$this->cells[$index]->moveTo($x, $y, $w, $h);
				}
			}
			if ($op) printf("·");

		}
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
 */

		for($a = 0; $a < $this->rows; $a++) {
			printf("·");
			lpf::updateStatus('Render',$a, $this->rows);
			for($b = 0; $b < $this->columns; $b++) {
				$index = ($a*$this->columns) + $b;
				$w = $this->cells[$index]->width;
				$h = $this->cells[$index]->height;
				$x = $this->cells[$index]->left;
				$y = $this->cells[$index]->top;
				// Create the next mutation
				$na = 0;
				for($aa = -1; $aa < 2; $aa++) { for($bb = -1; $bb < 2; $bb++) {
					// Get neighbouring node
					if (($aa!=0) && ($bb!=0)) {
						$nn = $this->getCellXY($a+$aa,$b+$bb);
						if (($nn) && ($nn->alive)) {
							$na++;
						}
					}
				}}
				$this->stage[$index] = $this->cells[$index];

				$algo = 'noccy';
				$alive = $this->stage[$index]->alive;

				switch($algo) {
				case 'noccy':
					if (($na < 2) && $alive) { $alive = false; }
					elseif (($na > 3) && $alive) { $alive = false; }
					elseif (($na > 2) && (!$alive)) { $alive = true; }
					break;
				default:
					if (($na < 2) && $alive) { $alive = false; }
					elseif (($na > 3) && $alive) { $alive = false; }
					elseif (($na == 3) && (!$alive)) { $alive = true; }
				}
				$this->stage[$index]->alive = $alive;

				// Draw the cell
				$rc = new Canvas($w,$h);
				$this->cells[$index]->neighbours = $na;
				$this->cells[$index]->render($ss,$as,$rc);
				$rc->draw($c, $x, $y, $w, $h);
			}
		}
		// Flip the stage
		$this->cells = $this->stage;

	}

}

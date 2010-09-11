<?php
/**
 * @file booki.php
 * @brief Boki - A book wiki
 *
 * Part of the Lepton NG codebase. Work together to create a book consisting
 * of chapters and sections, each treated as its own wiki segment.
 *
 * @copyright (c) 2010, NoccyLabs.info
 * @license GNU GPL v3
 */
 
class BokiController extends Controller {

	function index() {
	
		View::load('boki/index.php');
	
	}

}

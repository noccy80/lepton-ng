<?php

using('lpf.scenegraph');

interface ILpfScene {

}

abstract class LpfScene implements ILpfScene {

}

class Scene extends LpfScene {

	private $width = 0;
	private $height = 0;
	private $frame = 0;

    private $_scenegraph = null;

    public function __construct($width,$height,Color $background = null) {
        $this->_scenegraph = new SceneGraph();
        console::writeLn("Created scenegraph [%dx%d]", $width, $height);
    }

    public function getSceneGraph() {
        return $this->_scenegraph;
    }
    
    public function render($frame) {
        $this->_scenegraph->render($frame);
    }

}

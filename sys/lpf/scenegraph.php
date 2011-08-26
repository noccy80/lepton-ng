<?php

class SceneGraph {

    private $_rootnode = null;

    public function __construct() {
        $this->_rootnode = new SceneNode();
    }

	public function getRootNode() {
        return $this->_rootnode;
	}
	
	public function render($frame) {
	    $c = new Canvas(800,600);
	    foreach($this->_rootnode->getAllNodes() as $node) {
	        $node->render($frame);
	    }
	}

}

interface ILpfSceneNode { 
    function render($frame);
}

class SceneNode implements ILpfSceneNode {

    private $_nodes = array();

    public function getAllNodes() {
        return $this->_nodes;
    }

	public function length() {
        return count($this->_nodes);
	}
	
	public function addActor($id, ILpfObject $object, ILpfBlender $blender = null) {
	    $actor = new SceneActor($object, $blender);
	    $this->_nodes[$id] = $actor;
	    return $actor;
	}
	
	public function addAnimator($property,ILpfAnimator $animator,$frstart,$frend) {
	    $this->_animators[$property][] = array(
	        'animator' => $animator,
	        'framestart' => $frstart,
	        'frameend' => $frend
	    );
	    console::writeLn("Attached animator: %s => %s", typeOf($animator), $property);
	}
	
	public function render($frame) { }

}

class SceneActor extends SceneNode {
    public function render($frame) { }
    public function __construct() {
    }
}

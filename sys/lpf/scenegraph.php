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
	
	public function render($frame) { }

}

class SceneActor extends SceneNode {
	private $_object = null;
	private $_properties = null;
	private $_animators = array();
    public function __construct($object,$blender) {
    	$this->_object = $object;
    	$this->_properties = $object->getProperties();
    }
	public function addAnimator($property,ILpfAnimator $animator,$frstart,$frend) {
		if (arr::hasKey($this->_properties,$property)) {
			$this->_animators[$property][] = array(
			    'animator' => $animator,
			    'framestart' => $frstart,
			    'frameend' => $frend
			);
			console::writeLn("Attached animator: %s => %s", typeOf($animator), $property);
		} else {
			logger::warning("Animator attached to nonexisting property %s of object %s", $property, (string)$this->_object);
		}
	}
	public function render($frame) {
		$props = $this->_properties;
		foreach($this->_animators as $prop=>$anims) {
			foreach($anims as $anim) {
				// We aren't checking the framestart and frameend properties here
				$fi = $frame;
				$fe = 1000;
				$animator = $anim['animator'];
				$val = $animator->getValue($fi,$fe);
				console::write(" %s=%s  ", $prop, $val);
				$props[$prop] = $val;
			}
		
		}
		$framedata = array(
			'width' => $this->scene->width,
			'height' => $this->scene->height,
			'frame' => $frame
		);
		// TODO: Pass through blender
		return $this->_object->render($framedata,$props);
	}
}

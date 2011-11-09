<?php

interface ILpfTimeline {

}

class Timeline implements ILpfTimeline {

    private $_duration = 0;
    private $_scenes = array();

    public function __construct($frames) {
        $this->_duration = $frames;
        console::writeLn("Timeline of %d frames set up ...", $frames);
    }
    
    public function addScene(ILpfScene $scene, $frstart = 0, $frend = -1) {
        $this->_scenes[] = $scene;
    }
    
    public function renderTimeline() {
    
        $frlast = $this->_duration;
        for($fr = 0; $fr < $this->_duration; $fr++) {
            $frc = $fr + 1;
            $pc = (100 / $frlast) * $frc;
            console::write(str_repeat(chr(8),80)."[%d%%] Rendering frame %d of %d (%d to go)     ", $pc, $frc, $frlast, $frlast-$frc);
            $this->_scenes[0]->render($fr);
        }
        console::writeLn("\nDone!");
    
    }

}

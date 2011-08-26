<?php

using('lpf.renderer');
using('lpf.frame');

interface ILpfgRenderer {
    function render(ILpfTimeline $timeline) { }
}

abstract class LpfRenderer implements ILpfRenderer {

}

class DefaultLpfRenderer extends LpfRenderer {

}

<?php     module("Graphics Filter: Perspective Transform",array(
    'depends' => array(
        'lepton.graphics.canvas',
        'lepton.graphics.filter'
    )
));

class PerspectiveTransformFilter extends ImageFilter {

	const PC_TOPLEFT = 0;
	const PC_TOPRIGHT = 1;
	const PC_BOTTOMLEFT = 2;
	const PC_BOTTOMRIGHT = 3;

	private $sp = array(); // start pos
	private $ep = array(); // end pos
	private $dp = array(); // delta
	private $d = 0; // longest distance
	private $g = 0; // granularity

    function __construct(array $origin,array $dest) {
        // Make sure we got 8 values per array (4 coordinates), then 
        // precalculate the initial values.
		$this->sp = $origin;
		$this->ep = $dest;
		for ($n = 0; $n < 4; $n++) {
			$this->dp[$n] = $this->ep[$n] - $this->sp[$n];
		}
    }

    function applyFilter(Canvas $canvas) {
        // Create a new canvas to work with
		$dest = new Canvas($canvas->width, $canvas->height);
		for ($y = 0; $y < $canvas->width; $y++) {
			// Translate left and right coord based on y pos
			$ppy = (100 / $canvas->width) * $y;
			$rpy = $width / $ppx;
			for ($x = 0; $x < $canvas->height; $x++) {
				$ppx = (100 / $canvas->height) * $x;
				// Find relative spot in image, translate to source coordinate
				// space, and then to the transformed space.
			}
		}
    }

}

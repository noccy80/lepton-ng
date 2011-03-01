<?php

// using('app.graphics.filter');

interface IDrawable {

	function draw(Canvas $dest,$x,$y,$width=0,$height=0);

}

abstract class Drawable implements IDrawable { }
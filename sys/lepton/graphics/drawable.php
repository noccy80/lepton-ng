<?php

// using('app.graphics.filter');

interface IDrawable {

	function draw(Canvas $dest,$x=0,$y=0,$width=null,$height=null);

}

abstract class Drawable implements IDrawable { }

<?php

// using('app.graphics.filter');

interface IDrawable {

	function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null);

}

abstract class Drawable implements IDrawable { }

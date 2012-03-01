<?xml version="1.0" encoding="utf-8"?>
<scene>
	<script type="text/php" src="life.lp" />
	<canvas id="master" width="720" height="400" background="rgb(0,0,0)" />
	<target format="mp4" lossless="false" />

	<actor id="board" type="LifeGrid">
		<param key="rows" value="20" />
		<param key="columns" value="50" />
		<position left="10" top="10" width="700" height="350" />
	</actor>

	<actor id="footer1" type="Text" font="Delicious-Roman.ttf" size="16" text="Conway's Game of Life" color="#FFFFFF" background="#000000">
		<position left="20" top="360" width="400" height="30" />

	</actor>

	<actor id="footer2" type="Text" font="Delicious-Roman.ttf" size="12" text="Rendered with Lepton LPF" color="#FFFFFF" background="#000000">
		<position left="20" top="380" width="400" height="30" />
	</actor>

</scene>

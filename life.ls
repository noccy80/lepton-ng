<?xml version="1.0" encoding="utf-8"?>
<scene>
	<script type="text/php" src="life.lp" />
	<canvas id="master" width="1920" height="1080" background="rgb(0,0,0)" />
	<target format="mp4" lossless="false" />

	<actor id="board" type="LifeGrid">
		<param key="rows" value="40" />
		<param key="columns" value="95" />
		<position left="20" top="20" width="1880" height="950" />
	</actor>

	<actor id="footer1" type="Text" font="Delicious-Roman.ttf" size="20" text="Conway's Game of Life" color="#FFFFFF" background="#000000">
		<position left="20" top="990" width="400" height="30" />

	</actor>

	<actor id="footer2" type="Text" font="Delicious-Roman.ttf" size="16" text="Rendered with Lepton LPF" color="#FFFFFF" background="#000000">
		<position left="20" top="1030" width="400" height="30" />
	</actor>

</scene>

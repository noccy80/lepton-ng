<?xml version="1.0" encoding="utf-8"?>
<scene>

	<script type="text/php" src="life.lp" />
	<script type="text/php"><![CDATA[ 
	    echo "\nConway's Game of Life 1.0\n(c) 2012, NoccyLabs.info. Distributed under GNU GPL v3 or later.\n\n"; 
	    echo "Supports both classic ruleset and modern ruleset, see the help for more or use rule=<ruleset>\n\n";
	]]></script>
	<canvas id="master" width="720" height="400" background="rgb(0,0,0)" />
	<target format="mp4" lossless="false" />

	<actor id="board" type="LifeGrid">
		<param key="rows" value="20" />
		<param key="columns" value="50" />
		<position left="10" top="10" width="700" height="350" />
		<filter type="Blur" params="5" />
	</actor>

	<actor id="footer1" type="Text" font="Delicious-Roman.ttf" size="16" text="Conway's Game of Life" color="#FFFFFF" background="#000000">
		<position left="20" top="360" width="400" height="30" />

	</actor>

	<actor id="footer2" type="Text" font="Delicious-Roman.ttf" size="10" text="Rendered with Lepton LPF" color="#FFFFFF" background="#000000">
		<position left="20" top="380" width="400" height="30" />
	</actor>


	
	<actortemplate name="gameoflifeboard" type="LifeGrid">
		<param key="rows" value="20" />
		<param key="columns" value="50" />
		<position left="10" top="10" width="700" height="350" />
	</actortemplate>
	<actortemplate name="footer" type="Text" font="Delicious-Roman.ttf" color="#FFFFFF" background="#000000" />
	<!--
		Classes add properties to the various actors.
	-->
	<class name="Text:big" size="16" />
	<class name="Text:small" size="10" />
	<!--
		And this is our scenegraph.
	-->
	<scenegraph>
		<node id="root">
			<node id="board" protoype="board" />
			<node id="footer">>
				<node id="footer1" template="footer" class="big" text="Conway's Game of Life" />
				<node id="footer2" template="footer" class="small" text="Rendered with Lepton LPF" />
			</node>
		</node>
	</scenegraph>

</scene>

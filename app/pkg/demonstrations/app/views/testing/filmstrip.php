<?php Document::begin(Document::DT_HTML401_TRANS); ?>
<html lang="en-us">
<head>
    <title>lepton.ui.FilmStrip demo</title>
    <script src="/res/js/prototype.js" type="text/javascript"></script>
    <script src="/res/js/leptonjs/js.js" type="text/javascript"></script>
    <script src="/res/js/leptonjs/ui.js" type="text/javascript"></script>
    <script type="text/javascript">

        var images = [
            {    thumbSrc:'/res/filmstrip/one-t.png',
                fullSrc:'/res/filmstrip/one-f.png',
                texttitle:'The First Item.',
                textinfo:'This is the text for the first film strip item',
                name:'one' },
            {    thumbSrc:'/res/filmstrip/two-t.png',
                fullSrc:'/res/filmstrip/two-f.png',
                texttitle:'The Second Item.',
                textinfo:'This is the text for the second film strip item',
                name:'two' },
            {    thumbSrc:'/res/filmstrip/three-t.png',
                fullSrc:'/res/filmstrip/three-f.png',
                texttitle:'The Third Item.',
                textinfo:'This is the text for the third film strip item',
                name:'three' },
            {    thumbSrc:'/res/filmstrip/four-t.png',
                fullSrc:'/res/filmstrip/four-f.png',
                texttitle:'The Fourth Item.',
                textinfo:'This is the text for the fourth film strip item',
                name:'four' },
            {    thumbSrc:'/res/filmstrip/five-t.png',
                fullSrc:'/res/filmstrip/five-f.png',
                texttitle:'The Fifth Item.',
                textinfo:'This is the text for the fifth film strip item',
                name:'five' }
        ];
        var strip = null;

        Event.observe(window,"load",function(){
            // Create a new strip in the div named thumbs. 
            strip = new lepton.ui.FilmStrip("thumbs",{
                'thumbWidth':90,
                'thumbHeight':75,
                'width':600,
                'onClick':function(name,data) {
                    $('inner').update('<img src="'+data.thumbSrc+'"><p><strong>'+data.texttitle+'</strong></p><p>'+data.textinfo+'</p>');
                    $('display').setStyle({
                        backgroundImage:'url('+data.fullSrc+')'
                    });
                    strip.selectItem(name);
                },
                'data':images
            });
            // Start on number 0
            strip.selectByIndex(0);
            // ...but move on to the next one every 5 seconds
            this.striptimer = setInterval(function(){
                strip.goNextByIndex();
            }.bind(strip),5000);
            // ...unless the cursor is over the main box
            $('imagebox').observe("mouseover", function(){
                if (this.striptimer) clearInterval(this.striptimer);
                $('autotimer').hide();
            }.bind(this));
            // ...in which case we also need to get things going again when
            // the cursor leaves the main box
            $('imagebox').observe("mouseout", function() {
                if (this.striptimer) clearInterval(this.striptimer);
                this.striptimer = setInterval(function(){
                    strip.goNextByIndex();
                }.bind(strip),5000);
                $('autotimer').show();
            }.bind(this));

            // And that's it!'

        });


    </script>
    <style type="text/css">
        body {
            margin:0px;
            padding:25px;
        }
        #imagebox {
            width:600px;
            height:400px;
            background-color:#f8f8ff;
        }
        #imagebox > #display {
            width:140px;
            padding:0px 460px 0px 0px;
            color:#FFFFFF;
            font:8pt sans-serif;
            height:330px;
        }
        #imagebox > #display > div {
            background:rgba(0,0,0,0.8);
            padding:10px;
            width:120px;
            height:350px;
            text-align:center;
        }
        #imagebox > #thumbs {
            background-color:#FFFFFF;
            width:600px;
            height:75px;
            border-top:solid 5px #000000;
            overflow:hidden;
        }
        #imagebox > #thumbs > div {

        }

        .-ljs-ui-filmstrip-active {
            background:url(/res/filmstrip/strip-active.png);
        }
        .-ljs-ui-filmstrip-focus {
            background-color:#f0f0ff;
        }
    </style>
</head>
<body>

    <div id="imagebox">
        <div id="display"><img id="autotimer" style="float:right; position:relative; left:450px; top:8px;" src="/res/filmstrip/timer.gif"><div id="inner"></div></div>
        <div id="thumbs"></div>
    </div>

</body>
</html>

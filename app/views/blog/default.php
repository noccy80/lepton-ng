<?php document::begin(document:: DT_HTML401_TRANS);

?>
<html>
<head>
	<title>Blog - <?=$this-sitename?></title>
	<style type="text/css">
	body { 
		background:url(/res/blog/background.jpg);
	
	}
	
	#body {
		background:rgba(0,0,0,0.8);
		-moz-border-radius:10px;
		-opera-border-radius:10px;
		-webkit-border-radius:10px;
		padding:15px;
		color:#FF8800;
		font:8pt helvetica,arial,sans-serif;
	}
	
	h1 {
		font:bold 11pt helvetica,arial,sans-serif;
		color:#FFFFFF;
	}
	
	h2 { 
		font:bold 9pt helvetica,arial,sans-serif;
		color:#EEEEEE;
	}
	
	h1,h2,h3,p {
		margin:0px;
		padding:4px 0px 2px 0px;
	}
	
	.posttext {
		padding:3px 3px 3px 15px;
	}

	.postfoot {
		padding:3px 3px 3px 15px;
		color:#aa6600;
	}
	
	a { 
		color:#bb5500;
		text-decoration:none;
	}
	a:hover {
		color:#FF8800;
	}
	
	</style>
</head>
<body>

<!-- blog demo code begins, insert this on your site for a generic blog     -->

<!-------- 8< ---------------------------------------------------------------->

	<div id="body">
		<h1>Latest posts</h1>
		<? foreach($this->blogposts as $blogpost): ?>
		<h2><?=$blogpost['title']?></h2>
		<div class="posttext"><?=$blogpost['text']?></div>
		<div class="postfoot"><a href="/blog/comments/<?=$blogpost['slug']?>"><?=(int)$blogpost['comments']?> comments</a>, <a href="/blog/about/trackback"><?=(int)$blogpost['replies']?> replies</a></div>
		<? endforeach; ?>
	</div>

<!-------- 8< ---------------------------------------------------------------->

</body>
</html>

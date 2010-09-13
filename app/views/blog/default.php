<? $this->import("site/header.php"); ?>


<!-- blog demo code begins, insert this on your site for a generic blog     -->

<!-------- 8< ---------------------------------------------------------------->

	<div id="body" style="overflow:hidden; width:900px;">

	<!-- first column -->

		<div id="main" class="blackbox" style="width:600px; float:left;">
			<h1>Latest posts</h1>
			<? foreach($this->blogposts as $blogpost): ?>
			<h2><?=$blogpost['title']?></h2>
			<div class="posttext"><?=$blogpost['text']?></div>
			<div class="postfoot"><a href="/blog/comments/<?=$blogpost['slug']?>"><?=(int)$blogpost['comments']?> comments</a>, <a href="/blog/about/trackback"><?=(int)$blogpost['replies']?> replies</a></div>
			<? endforeach; ?>
		</div>
		
	<!-- second column -->
		
		<div id="right" class="blackbox" style="float:left; width:190px; margin-left:10px;">
			<p>Browse by date:</p>
			<!-- calendar widget -->
			<p>Browse by category:</p>
			<ul>
				<li><a href="#">Uncategorized</a></li>
			</ul>
			<p>Browse by tags:</p>
			<ul>
				<li><a href="#">untagged</a></li>
			</ul>
		</div>
	<!-- end body div -->

	</div>

<!-------- 8< ---------------------------------------------------------------->

</body>
</html>

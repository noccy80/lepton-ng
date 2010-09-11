<?	$this->includeView('boki/include/header.php'); ?>
<div id="logic-wrap">
	<div id="logic-header" style="overflow:hidden;">
		<div style="float:left; height:100px;">
			<img src="/res/boki/logo.png">
		</div>
		<div style="float:left; padding-top:80px;">
			<a class="button" href="javascript:boki.book.open();">Open a book</a>
		</div>
	</div>
	<div id="logic-cont">

		<div class="-column sidemenu" style="width:195px;">
			<div class="strong">Book</div>
				<a href="#">Authors</a>
				<a href="#">Cover and Metadata</a>
				<a href="#">Indexes and Tables</a>
			<div class="strong">Chapter</div>
				<p><a href="#">About</a> &rsaquo; <a href="#">Authors</a> &rsaquo; <strong>Alice</strong></p>
				<a href="#">Add chapter</a>
				<a href="#">Reorder chapters</a>
		</div>
	
		<div class="-column" style="width:600px;">
	
			<div>

				<select style="width:250px;">
					<option value="1-1-2" selected>About &rsaquo; Authors &rsaquo; Alice</option>
					<option value="">&nbsp;</option>
					<option value="1">1. About</option>
					<option value="1-1"> &nbsp; &nbsp; 1a. Authors</option>
					<option value="1-1-1"> &nbsp; &nbsp; &nbsp; &nbsp; 1a1. Dan</option>
					<option value="1-1-2" style="font-weight:bold;">&nbsp; &nbsp; &nbsp; &nbsp; 1a2. Alice</option>
					<option value="1-1-3"> &nbsp; &nbsp; &nbsp; &nbsp; 1a3. Bob</option>
					<option value="1-1-3-1"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 1a3a. History</option>
					<option value="1-1-4"> &nbsp; &nbsp; &nbsp; &nbsp; 1a4Joe</option>
				</select>
				<input type="button" value="Add after">
				<input type="button" value="Add under">
				<input type="button" value="Remove chapter">

			</div>

			<div id="book-content">
				<p><b>(CNN)</b> -- President Obama acknowledged Friday that bouncing back from the recession has been &quot;painfully slow,&quot; but he insisted that the economy continues to grow as he pushed his administration's new economic proposals at his first news conference in months.</p>
				<p>Obama once again urged the Senate to pass his small-business jobs bill, saying it has been blocked by &quot;a partisan (Republican) minority.&quot; He praised Sen. George Voinovich, R-Ohio, for announcing that he would not help GOP leaders block the bill.</p>
				<p>Still, he said, there is &quot;room for discussion&quot; on competing tax plans. &quot;If the Republican leadership is prepared to get serious ... I would love to talk to them,&quot; he told reporters at the White House.</p>
				<p>Obama insisted, however, that the GOP plan to extend Bush-era tax cuts for individuals earning more than $250,000 is a bad idea. He again accused Republicans of holding middle-class income tax cuts &quot;hostage&quot; by tying them to an extension of Bush tax cuts for wealthier Americans.</p>
				<p>Asked about the upcoming November elections, Obama said that if they are merely a referendum on the economic progress made so far, people will say &quot;we're not there yet&quot; -- implying that Democrats will not fare well. But if the election presents a clear contrast between GOP and Democratic policies, he said, Democrats will succeed at the polls.</p>
			</div>
	
		</div>
	
	</div>
</div>

<?	$this->includeView('boki/include/footer.php'); ?>

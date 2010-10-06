<?php document::begin('html/4.01'); ?>
<html lang="en-us">
    <head>
        <title><?=LEPTON_PLATFORM_ID?></title>
    </head>
    <body>
    	<h1>Test results</h1>
    	<?	using('lepton.utils.tests'); ?>
    	<?	using('tests.*'); ?>
    	<?	using('app.tests.*'); ?>
    	<?	
    		class HtmlReporter extends TestReporter {
    			function beginGroup($title) {
    				printf('<h2>%s</h2>',$title);
    				printf('<table><tr>');
    				foreach(array(
    					'Test' => 50,
    					'Text' => 300,
    					'Status' => 90
    				) as $title=>$width) printf('<th width="%d">%s</th>', $width, $title);
    				printf('</tr>');
    			}
    			function addItem($index,$text,$status) {
    				printf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>', $index, $text, $status);
    			}
    			function endGroup() {
    				printf('</table>');
    			}
    		}
    		TestRunner::run(new HtmlReporter());
    	?>
        <p>If you can read this, Lepton-ng is working!</p>
    </body>
</html>

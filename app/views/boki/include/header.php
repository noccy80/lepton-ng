<?
	Document::begin(Document::DT_HTML401_TRANS);
	$currentbook = 1; // TODO: initialize from session
	$currentbookdata = array(
		'id' => 1,
		'slug' => 'default',
		'title' => 'Demonstration Boki'
	);
	if ($currentbook) {
		$booktitle = $currentbookdata['title'];
	} else {
		$booktitle = "No book open";
	}
	KeyStore::register('api:google','ABQIAAAAX0duTJDNnIGr0q-PMsNgPBQGZChJCJZDmT1j5LVvleoaQrnX6hSGrc-x9nyNu0-Z5W4PyN81K-Oa4w');
?>
<html lang="en-us">
<head>
	<title>BOKi: <?=$booktitle?></title>
	<script type="text/javascript" src="http://www.google.com/jsapi?key=<?=KeyStore('api:google')?>"></script>
	<script type="text/javascript">
		google.load("prototype", "1.6.1.0");
	</script>
	<link rel="stylesheet" type="text/css" href="/res/boki/boki.css">
	<script type="text/javascript" src="/res/boki/boki.js"></script>
</head>
<body>

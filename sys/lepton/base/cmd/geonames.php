<?php __fileinfo("Geonams Console Actions", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

/*
	This file is part of Lepton Framework.
	Copyright (C) 2001-2010  Noccy Labs

	Lepton Framework is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Lepton Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Warzone 2100; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/



/**
 * @class GeonamesUtility
 * @brief Handles import and updates of a GeoNames table containing country
 *   and region information.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @copyright (c) 2001-2010, Noccy Labs
 * @license GPL v3
 */
class GeonamesUtility extends ConsoleActions {

    static $help = array(
        'geonames' => 'Manage, download and import geonames data'
    );
    function _info($cmd) { return self::$help[$cmd->name]; }

	function geonames($cmd=null, $args=null) {
		using('lepton.db.*');
		switch($cmd) {
			case 'import':
				$tm = new TextMenu('Select the sets to import');
				foreach(GeonamesImporter::$importers as $importer) {
					$tm->addOption($importer->key, $importer->menudescription, true);
				}
				if ($tm->runMenu()) {
					foreach(GeonamesImporter::$importers as $importer) {
						if ($tm->getOption($importer->key)) {
							$sets = $importer->getAvailableDatasets();
							$sm = new TextMenu("What countries would you like to import?");
							$sm->setLayout(8,5);
							foreach($sets as $lang=>$seturl) {
								$sm->addOption($lang,$lang,true);
							}
							if ($sm->runMenu()) {
								foreach($sets as $lang=>$seturl) {
									if ($sm->getOption($lang)) $importer->importDataset($lang);
								}
							}
						}
					}
				}
				break;		
			case 'download':
				$tm = new TextMenu('Select the sets to download');
				foreach(GeonamesImporter::$importers as $importer) {
					$tm->addOption($importer->key, $importer->menudescription,true);
				}
				if ($tm->runMenu()) {
					foreach(GeonamesImporter::$importers as $importer) {
						if ($tm->getOption($importer->key)) {
							$sets = $importer->getAvailableDatasets();
							$sm = new TextMenu("What countries would you like to download?");
							$sm->setLayout(8,5);
							foreach($sets as $lang=>$seturl) {
								$sm->addOption($lang,$lang,true);
							}
							if ($sm->runMenu()) {
								foreach($sets as $lang=>$seturl) {
									if ($sm->getOption($lang)) $importer->cacheDataset($lang);
								}
							}
						}
					}
				}
				break;		
			default:
				console::writeLn(__astr('geonames \b{import} [\b{all}|\b{countries}|\b{languages}|\u{lc} [\u{lc}] ..]'));
				console::writeLn(__astr('geonames \b{list} -- List all countries available'));
				console::writeLn(__astr('geonames \b{reset} -- Resets all data, effectively clearing the database'));
		}
	}
	

	/**
	 * @brief Get the index of available files from GeoNames
	 *
	 * @return array The files available
	 */
	private function getIndex() {

		console::write("Updating index");

		$f = file_get_contents($this->geonames);
		$h = new DomDocument();
		$h->loadHtml($f);
		$a = $h->getElementsByTagName('a');
		$ret = array();
		$blocked = array(
			'allCountries.zip'
		);
		for($n = 0; $n < $a->length; $n++) {
			$url = $a->item($n)->getAttribute('href');
			if ((strToUpper($url[0])>='A') && (strToUpper($url[0])<='Z') && (strpos($url,'/') === false)) {
				if (!in_array($url,$blocked)) {
					$ret[] = $this->geonames.$url;
				}
			}
		}
		$fh = fopen(APP_PATH.'/geonames.cachedb','wb');
		fwrite($fh,serialize($ret));
		fclose($fh);
		return $ret;
		console::writeLn("Parsed");

	}
	
	

}

interface IGeonamesImporter {
	function importDataSet($set);
	function cacheDataSet($set);
	function getAvailableDatasets();
}
abstract class GeonamesImporter implements IGeonamesImporter {
	static $importers = array();
	protected $geonames = 'http://download.geonames.org/export/dump/';
	protected function getCacheFile() {
		return APP_PATH.'/geonames.db';
	}
	protected function getIndex() {
		if (!file_exists($this->getCacheFile())) {
			GeonamesImporter::updateIndex();
		} else {
			$res = unserialize(file_get_contents($this->getCacheFile()));
			if (!isset($res['created']) || (time() - $res['created'] > 3600)) {
				$this->updateIndex();
			}
		}
		$res = unserialize(file_get_contents($this->getCacheFile()));
		return $res['data'];
		
	}
	protected function updateIndex() {
		console::write("Updating index: ");

		$f = @file_get_contents($this->geonames);
		if (!$f) {
			console::writeLn("Failed.");
			console::writeLn(get_last_error());
		}
		$h = new DomDocument();
		$h->loadHtml($f);
		$a = $h->getElementsByTagName('a');
		$ret = array();
		$blocked = array(
			'allCountries.zip'
		);
		for($n = 0; $n < $a->length; $n++) {
			$url = $a->item($n)->getAttribute('href');
			if ((strToUpper($url[0])>='A') && (strToUpper($url[0])<='Z') && (strpos($url,'/') === false)) {
				if (!in_array($url,$blocked)) {
					$ret['data'][] = $this->geonames.$url;
				}
			}
		}
		$fh = fopen($this->getCacheFile(),'wb'); 
		$ret['created'] = time();
		fwrite($fh,serialize($ret));
		fclose($fh);

		console::writeLn("Done");

		return $ret['data']; 
	
	}
	/**
	 * @brief Method to handle downloading of the needed files
	 *
	 */
	protected function download($file) {

		if (!file_exists(APP_PATH.'geonames.cache')) {
			mkdir(APP_PATH.'geonames.cache');
		}

		$dest = APP_PATH.'geonames.cache/'.basename($file);
		console::write("Downloading %s: ", basename($file));
		$fd = fopen($dest,'wb');
		$fr = fopen($file,'rb');
		$size = 0;
		if (($fr) && ($fd)) {
			while (!feof($fr)) {
				$data = fread($fr,8192);
				fwrite($fd,$data);
				$size+=strlen($data);
			}
		} else {
			die("Error!");
		}
		console::write("%d bytes ... ", $size);
		fclose($fd);
		fclose($fr);
		console::writeLn("Done");
	}

	protected function insertBatch($batch) {

		$db = new DatabaseConnection();
		// $prefix = ($this->hasArgument('p')?$this->getArgument('p'):'');
		$prefix = '';

		$sql = 'REPLACE INTO '.$prefix.'geonames VALUES ';

		$rowdata = array();
		foreach($batch as $row) {
			foreach($row as $id=>$data) {
				$row[$id] = $db->quote($data);
				// $row[$id] = str_replace("'","\\'",$data);
				// $row[$id] = str_replace("%","%%'",$data);
			}
			$rowdata[] = "(".join(",", $row).")";
		}
		$this->records+=count($rowdata);
		$sql.= join(',',$rowdata);
		// if ($this->verbose) var_dump($sql);
		try {
		$db->exec($sql);
		} catch (Exception $e) {
			// var_dump($sql);
			echo $e;
			die();
		}

	}
}

class CountryImporter extends GeonamesImporter {
	var $menudescription = 'Import country information using CountryImporter';
	var $key = 'g';
	private $sets = array();
	function getAvailableDatasets() {
		$this->createTable();
		$data = $this->getIndex();
		foreach($data as $item) {
			if (fnmatch('*/??.zip',$item)) {
				if (preg_match('/\/(.{2}).zip$/', $item,$ct)) {
					$country = strtolower($ct[1]);
					$this->sets[$country] = $item;
				}
			}
		}
		return $this->sets;
	}
	private function createTable() {
		//$prefix = ($this->hasArgument('p')?$this->getArgument('p'):'');
		$prefix = '';
		/*
		switch(strtolower($this->getArgument('t'))) {
			case null:
			case 'innodb':
				$tabletype = 'InnoDB';
				break;
			case 'myisam':
				$tabletype = 'MyISAM';
				break;
			default:
				console::writeLn("Table type must be 'innodb' or 'myisam'.");
				die(RETURN_ERROR);
				break;
		}
		*/
		$tabletype = 'innodb';

		$db = new DatabaseConnection();
		// if ($this->hasArgument('f')) {
		//	$sql = 'DROP TABLE IF EXISTS '.$prefix.'geonames';
		//	$db->exec($sql);
		// }
		$sql = 'CREATE TABLE IF NOT EXISTS '.$prefix.'geonames ('.
				'id INT NOT NULL PRIMARY KEY, '.
				'name VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci, '.
				'asciiname VARCHAR(200), '.
				'alternatenames VARCHAR(200), '.
				'latitude DECIMAL(9,5), '.
				'longitude DECIMAL(9,5), '.
				'featureclass CHAR(1), '.
				'featurecode VARCHAR(10), '.
				'countrycode CHAR(2), '.
				'cc2 VARCHAR(60), '.
				'admin1code VARCHAR(20), '.
				'admin2code VARCHAR(80), '.
				'admin3code VARCHAR(20), '.
				'admin4code VARCHAR(20), '.
				'population BIGINT, '.
				'elevation INT, '.
				'gtopo30 INT, '.
				'timezoneid VARCHAR(64), '.
				'modificationdate DATE, '.
				'INDEX name(name), '.
				'INDEX countrycode(countrycode), '.
				'INDEX latlong(latitude,longitude), '.
				'INDEX features(featureclass,featurecode), '.
				'INDEX admincodes(admin1code,admin2code,admin3code,admin4code), '.
				'INDEX population(population), '.
				'INDEX timezoneid(timezoneid)'.
				') TYPE='.$tabletype.' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
		console::write("Creating tables: ");
		$db->exec($sql);
		console::writeLn("Done");
	}
	public function cacheDataSet($cc) {
		$dest = APP_PATH.'geonames.cache/'.strtoupper($cc).'.zip';
		if (!file_exists(APP_PATH.'geonames.cache/'.strtoupper($cc).'.txt.gz')) {
			if (!file_exists($dest)) {
				$this->download($this->sets[$cc]);
			}
			console::write("Recompressing archive: ");
			$fz = fopen('zip://'.$dest.'#'.basename($dest,'.zip').'.txt','rb');
			$fo = gzopen(APP_PATH.'geonames.cache/'.basename($dest,'.zip').'.txt.gz','w5');
			if (($fz) && ($fo)) {
				while (!feof($fz)) {
					$data = fread($fz,8192);
					gzwrite($fo,$data);
				}
				console::writeLn("Done");
			} else {
				die("Error");
			}
			gzclose($fo);
			fclose($fz);
			// Delete zipfile after download
			unlink($dest);
		}
	}
	public function importDataSet($cc) {
		$dest = APP_PATH.'geonames.cache/'.strtoupper($cc).'.zip';
		$this->cacheDataSet($cc);
		$fh = fopen('compress.zlib://'.APP_PATH.'geonames.cache/'.strtoupper($cc).'.txt.gz','r');
		if (!$fh) { die("Error!"); }
		$batch = array();
		$batchsize = 2048;
		console::write("Importing %s: ", $cc);
		while (!feof($fh)) {
			$l = fgetcsv($fh,8192,"\t");
			if (count($l) == 19) $batch[] = $l;
			if (count($batch) >= $batchsize) { 
				console::write('.');
				$this->insertBatch($batch);
				$batch = array();
			}
		}
		if (count($batch) > 0) { 
			$this->insertBatch($batch);
		}
		console::writeLn(". Done");
		fclose($fh);
	}
}
GeonamesImporter::$importers[] = new CountryImporter();

class TextMenu {
	private $title;
	private $options = array();
	private $columns = null;
	private $width = null;
	function __construct($title,array $options=null) {
		$this->title = $title;
		if ($this->options) $this->options = $options;
	}
	function addOption($key,$value,$default) {
		$this->options[$key] = array(
			'value' => $value,
			'default' => $default,
			'state' => $default
		);
	}
	function getOption($key) {
		return $this->options[$key]['state'];
	}
	function setLayout($columns=null,$width=null) {
		$this->columns = $columns;
		$this->width = $width;
	}
	function runMenu() {
		while (true) {
			$col = 0;
			console::writeLn($this->title);
			$keys = array();
			foreach($this->options as $key=>$option) {
				if ($this->width) {
					console::write(' [%s] %-'.$this->width.'s', ($option['state'])?__astr('\b{x}'):' ', $key);
				} else {
					console::writeLn('   %s. [%s] %s', $key, ($option['state'])?__astr('\b{x}'):' ', $option['value']);
				}
				if ($this->columns) {
					$col++;
					if ($col > $this->columns) {
						$col = 0;
						console::writeLn();
					}
				}
				$keys[] = $key;
			}
			if (($this->columns) && ($col > 0)) {
				console::writeLn();
			}
			if (count($keys) > 10) {
				$keys = array_merge(array_slice($keys,0,9),array('...'));
			}
			$keystr = join('/',$keys);
			$prompt = '[Y/n/help/all/none/invert/reset/'.$keystr.']: ';
			$rl = readline::read($prompt);
			foreach(explode(' ',$rl) as $r) {
				foreach($this->options as $key=>$option) {
					if ($key == $r) {
						$this->options[$key]['state'] = !$this->options[$key]['state'];
					}
				}
				if (strtolower($r) == 'help') {
					console::writeLn("Type any of the alternatives listed above. They will be evaluated in the order they are provided.");
					console::writeLn("When you are done, hit enter on an empty line or enter 'y'. To cancel, enter 'n'.");
				}
				if ((strtolower($r) == 'y') || ($r == '')) {
					return true;
				}
				if (strtolower($r) == 'n') {
					return false;
				}
				if (strtolower($r) == 'all') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = true;
					}
				}
				if (strtolower($r) == 'none') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = false;
					}
				}
				if (strtolower($r) == 'reset') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = $this->options[$key]['default'];
					}
				}
				if (strtolower($r) == 'invert') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = !$this->options[$key]['state'];
					}
				}
			}
		}
	}
}

using('lepton.console.readline');
config::push('lepton.cmd.actionhandlers','GeonamesUtility');


<?php

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
	along with the software; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

__fileinfo("Geonames Console Actions", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

using('lepton.net.httprequest');

/**
 * @class GeonamesUtility
 * @brief Handles import and updates of a GeoNames table containing country
 *   and region information.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @copyright (c) 2001-2010, Noccy Labs
 * @license GPL v3
 */
class GeonamesAction extends Action {

    private $data = array();
	// private $baseurl = 'http://download.geonames.org/export/dump/';
	private $baseurl = 'http://public.hubea.se/geocache/';
	private $ignore = array(
		'allcountries.zip',
		'name'
	);

    function __construct() {
        if (!file_exists(base::appPath().'/.geodb')) {
            $this->data = array();
        } else {
            $this->data = unserialize(gzuncompress(file_get_contents(base::appPath().'/.geodb')));
        }
    }
    
    function __destruct() {
        file_put_contents(base::appPath().'/.geodb',gzcompress(serialize($this->data)));
    }
    
    

	public static $commands = array(
        'import' => array(
            'arguments' => '[\u{geo} [\g{cc},[\g{cc}]..]|\u{isocc}|\u{alias}]',
            'info' => 'Import geonames data set'
        ),
        'remove' => array(
            'arguments' => '[\u{geo} [\g{cc},[\g{cc}]..]|\u{isocc}|\u{alias}]',
            'info' => 'Remove specific sets',
        ),
        'sets' => array (
            'arguments' => '',
            'info' => 'List available sets'
        ),
        'purge' => array(
            'arguments' => '',
            'info' => 'Remove all geonames data from the database'
        ),
        'download' => array(
            'arguments' => '',
            'info' => 'Download (but don\'t import) the geonames data set'
        ),
		'update' => array(
			'arguments' => '',
			'info' => 'Update the geonames tables'
		),
        'lookup' => array(
            'arguments' => '\g{location}',
            'info' => 'Look up a specific location'
        ),
        'status' => array(
            'arguments' => '',
            'info' => 'Show geo status'
        )
    );

    /**
     * Look up a specific location.
     *
     * Pass feature code as second parameter to limit the search. Default is
     * all types returned.
     */
    public function lookup($location=null,$type=null) {
        $db = new DatabaseConnection();
        if ($location) {
            $rs = $db->getRows("SELECT * FROM geonames WHERE name=%s", $location);
            console::writeLn(__astr("\b{%8s %-30s %2s %-1s %-10s %-10s %-10s}"), 'Id', 'Name', 'CC', 'F', 'Code', 'Latitude', 'Longitude');
            foreach((array)$rs as $row) {
                console::writeLn("%8d %-30s %2s %1s %-10s %3.7f %3.7f", $row['id'], $row['name'], $row['countrycode'], $row['featureclass'], $row['featurecode'], $row['latitude'], $row['longitude']);
            }
        }
    }

    /**
     *
     *
     *
     *
     */
    function sets() {
        if (!$this->data['sets']) {
            $this->update();
        }
    }
    
    function update() {
        console::write("Updating set list: ");
		$f = file_get_contents($this->baseurl);
        $start = strpos('<img ',$f);
		$fd = substr($f, $start);
        $fd = str_replace("\t", " ", $fd);
		while(strpos($fd,'  ') !== false) $fd = str_replace('  ',' ',$fd);
        $entries = explode("\n", $fd);
		$this->data['fsprevious'] = (isset($this->data['fscurrent']))?$this->data['fscurrent']:array();
		$this->data['fscurrent'] = array();
		foreach ($entries as $ent) {
            $ents = explode(' ',trim($ent));
			if (count($ents) >= 8) {
				if ($ents[4] == '<a') {
					$fn = $ents[5];
					$fs = $ents[8];
					$fns = explode('>',$fn);
					$fns = explode('<',$fns[1]);
					$fns = $fns[0];
					if (strtolower($fns) != 'name')
    					$this->data['fscurrent'][$fns] = $fs;
				} elseif ($ents[3] == '<a') {
					$fn = $ents[4];
					$fs = $ents[7];
					$fns = explode('>',$fn);
					$fns = explode('<',$fns[1]);
					$fns = $fns[0];
					if (strtolower($fns) != 'name')
					$this->data['fscurrent'][$fns] = $fs;
				} else {
//				    console::writeLn($ent);
				}
			}
        }
		console::writeLn("Parsed");
		$this->data['update'] = array();
		console::write("Finding updated files: ");
		foreach($this->data['fscurrent'] as $fn=>$fs) {
			if (isset($this->data['fsprevious'][$fn])) {
				$prev = $this->data['fsprevious'][$fn];
			} else {
				$prev = null;
			}
			if ($prev != $fs) {
			    if (!in_array(strtolower($fn), $this->ignore)) {
    				$this->data['update'][$fn] = true;
    			}
			}
		}
		console::writeLn("%d sets", count($this->data['update']));
        
    }

    function createTables() {
        $db = new DatabaseConnection();
		$sql = 'CREATE TABLE IF NOT EXISTS geonames ('.
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
				') TYPE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci';
		console::write("Creating tables: ");
		$db->exec($sql);
		console::writeLn("Done");
    }

	function doupdate() {

        $this->createTables();

		console::writeLn("Downloading updated sets...");

		$e = str_repeat("\x08", 70);
		$ef = str_repeat("\x08", 70).str_repeat(" ",70).str_repeat("\x08",70);
		$tot = count($this->data['update']);

		$c = 0;
		foreach($this->data['update'] as $fn=>$void) {
			$this->total = array(
			    'max' => $tot,
			    'current' => $c++,
			    'percent' => (100/$tot) * $c
			);
			$this->activity = "Downloading";
			$this->activityobject = $fn;
			$dl = new HttpDownload($this->baseurl.$fn, base::appPath().'/geocache/'.$fn, array(
			    'onprogress' => new Callback(&$this,'onprogress')
			));
			$this->handleUpdateFile($fn);
			$this->clearTask();
		}

	}
	
	function handleUpdateFile($fn) {
	    if (fnmatch('??.zip',$fn)) {
	        $this->geoLocationInsert($fn);
	    } else {
	        $this->clearTask();
	        console::writeLn("Dont know what to do with %s...", $fn);
	    }
	}

//////// GEONAMES TABLE DATA //////////////////////////////////////////////////

	function geoLocationInsert($fn) {

		$this->progress = null;
    	$this->activity = "Importing";
		$this->doTaskUpdate();
		$dest = base::appPath().'/geocache/'.$fn;
        $fz = fopen('zip://'.$dest.'#'.basename($dest,'.zip').'.txt','rb');	
        if (!$fz) {
            console::fatal("Could not open file %s (%s)", $dest, $fn);
        }
        $this->doTaskUpdate();
        $batch = array();
        $rowstot = 0;
        while(!feof($fz)) {
            $row = fgetcsv($fz,16000,"\t",'*');
            $batch[] = $row;
            $rowstot++;
            if (count($batch) >= 50) {
                $this->progress['read'] += count($batch);
                $this->doTaskUpdate();
                $this->geoLocationInsertBatch($batch);
		        $batch = array();
		    }
        }
        $this->clearTask();
        console::writeLn("%s imported, %d rows.", $fn, $rowstot);
	}
	
	function geoLocationInsertBatch($batch) {
        $db = new DatabaseConnection();
        $sql = 'REPLACE INTO geonames VALUES ';
        $rowdata = array();
        foreach($batch as $row) {
	        foreach($row as $id=>$data) {
		        $row[$id] = $db->quote($data);
	        }
	        $rowdata[] = "(".join(",", $row).")";
        }
        $this->records+=count($rowdata);
        $sql.= join(',',$rowdata);
        try {
	        $db->exec($sql);
        } catch (Exception $e) {
	        echo $e;
	        die();
        }
    }	

//////// STATUS UPDATES ///////////////////////////////////////////////////////
	
	function clearTask() {
		$ef = str_repeat("\x08", 70).str_repeat(" ",70).str_repeat("\x08",70);
        console::write($ef);
        $this->progress = null;
	}
	function doTaskUpdate() {
		$e = str_repeat("\x08", 70);
        if ($this->progress == null) { $this->progress = array('percent' => 0, 'length' => 0, 'read' => 0); }
        if (!isset($this->progress['length']) || ($this->progress['length'] == 0)) {
    		console::write("[%3d%%] %-20s %-15s %8d    ", $this->total['percent'], $this->activity, $this->activityobject, $this->progress['read'] );
        } else {
    		console::write("[%3d%%] %-20s %-15s %3d%%    ", $this->total['percent'], $this->activity, $this->activityobject, $this->progress['percent'] );
		}
        console::write($e);
    }	
	
	function onprogress($max,$cur) {
	    $this->progress = array(
	        'length' => $max,
	        'read' => $cur,
	        'percent' => ($max>0)?round((100/$max)*$cur,2):0
	    );
	    $this->doTaskUpdate();
	}

    /**
     *
     *
     *
     *
     */
    function status() {
        console::writeLn("%-25s: %d (%d available)", 'Sets installed', count($this->data['installed']), count($this->data['available']));
        console::writeLn("%-25s: %s", 'Last update', 'never');
        console::writeLn("%-25s: %s", 'Table status', '0 KB');
    }

    /**
     *
     *
     *
     *
     */
    public function alias() {
        console::writeLn("Generating geoalias table");
        $db = new DatabaseConnection();
			$db->exec('DROP TABLE IF EXISTS geoalias');
			$db->exec('CREATE TABLE geoalias (id INT PRIMARY KEY AUTO_INCREMENT, geoid BIGINT, locname VARCHAR(64) CHARSET utf8, INDEX locname(locname(5))) CHARSET utf8');
			$rows = $db->getRows("SELECT id,alternatenames FROM geonames WHERE alternatenames!=''");
			console::write('%8d / %8d ', 0, count($rows));
			foreach($rows as $row) {
				$alt = explode(',',$row['alternatenames']);
				foreach($alt as $altstr) {
					$db->insertRow("INSERT INTO geoalias (geoid,locname) VALUES (%d,%s)", $row['id'], $altstr);
				}
				$rc++; $rt++;
				if ($rt>=100) {
					$rh++;
					if ($rh >= 50) {
						console::write("\n%8d / %8d ", $rc, count($rows));
						$rh = 0;
					} else {
						console::write('.');
					}
					$rt = 0;
				}

			}
			console::writeLn(' Done!');
    }

	private function getCacheFile() {
		return base::appPath().'/.gncachce';
	}

	public function updateCache() {
		console::write("Updating cache: ");

		$f = @file_get_contents($this->baseurl);
		if (!$f) {
			console::writeLn("Failed.");
			console::writeLn(get_last_error());
		}
		$h = new DomDocument();
		$h->loadHtml($f);
		$a = $h->getElementsByTagName('a');
		$ret = array();
		for($n = 0; $n < $a->length; $n++) {
			$url = $a->item($n)->getAttribute('href');
			if ((strToUpper($url[0])>='A') && (strToUpper($url[0])<='Z') && (strpos($url,'/') === false)) {
				if (!in_array($url,$blocked)) {
					$ret['data'][] = $this->url_base.$url;
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

	public function download() {
        
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

Actions::register(
    new GeonamesAction(),
    'geonames',
    'Manage the geonames table',
    GeonamesAction::$commands
);

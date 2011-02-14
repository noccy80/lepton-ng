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
	private $baseurl = 'http://download.geonames.org/export/dump/';
	private $ignore = array(
		'allCountries.zip'
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
		$blocked = array(
			'allCountries.zip'
		);
        $start = strpos('<img ',$f); $fd = substr($f, $start);
        while(strpos('  ',$fd)) $fd = str_replace('  ',' ',$fd);
        $entries = explode("\n", $fd);
        foreach ($entries as $ent) {
            $ents = explode(' ',trim($ent));
            if ((count($ents) >= 3) && (substr(0,3,$ents))) {
                console::writeLn(" -> %s", $ents[3]);
            }
            
        }
		console::writeLn("Parsed");
        
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

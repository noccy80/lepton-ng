<?php

abstract class GeoNames {

	private static function getUrl($url) {
		return 'http://download.geonames.org/export/dump/'.$url;
	}

	public static function updateCountryInfo(callback $callback = null) {
		// Open database connection and request the updated countryinfo
		$db = new DatabaseConnection();
		if ($callback) $callback->call('Downloading',0,1);
		$ci = new HttpRequest(self::getUrl('countryInfo.txt'));
        
		// When we have the countryinfo, go over and replace the entries
		// in the database.
		$ent = explode("\n",$ci->getResponse());
		$index = 0;
		foreach($ent as $entl) {
			// Call on the callback if defined
			if ($callback) $callback->call('Importing',++$index,count($ent));
			if ((substr($entl,0,1) != '#') && (trim($entl) != '')) {
				$cols = explode("\t", $entl);
				// Insert the parsed rows in the database
				$db->updateRow(
					'REPLACE INTO geonames_countryinfo '.
					'(isocode,iso3code,isonumeric,fips,'.
					'country,capital,area,population,continent,'.
					'tld,currencycode,currencyname,phone,'.
					'postalcode,postalcoderegex,languages,geoid,'.
					'neighbours,equivalentfips) '.
					'VALUES '.
					'(%s,%s,%s,%s, %s,%d,%d,%s, '.
					'%s,%s,%s,%s,%s, %s,%s,%s,%d, '.
					'%s,%s)',
					$cols[0], $cols[1], $cols[2], $cols[3],
					$cols[4], $cols[5], $cols[6], $cols[7],
					$cols[8], $cols[9], $cols[10], $cols[11],
					$cols[12], $cols[13], $cols[14], $cols[15],
					$cols[16], $cols[17], $cols[18]
				);
			}
		} // end foreach

		if ($callback) $callback->call('Country info imported OK');
		self::updateSets($callback);

	}
    
    public static function updateAdminCodes(callback $callback = null) {

        $db = new DatabaseConnection();
        
        cb($callback,'Downloading admin1codes',0,1);
        $url = self::getUrl('admin1CodesASCII.txt');
        $req = new HttpRequest($url);

        $reqs = explode("\n", $req->getResponse());
        $rows = 0; $ltime = 0;
        foreach($reqs as $reql) {
            $rd = explode("\t",trim($reql));
            if (count($rd)>1) {
                $db->updateRow(
                    "REPLACE INTO geonames_admin1codes ".
                    "(admin1code,name,longname,geoid) VALUES (%s,%s,%s,%d)",
                    $rd[0],$rd[1],$rd[2],$rd[3]);
                if (microtime(true) > $ltime+1) {
                    cb($callback,'Importing admin1codes ... '.$rows." records imported", 1);
                    $ltime = microtime(true);
                }
                $rows++;
            }
        }
        cb($callback,"Imported admin1codes (".$rows." rows)");
        
        cb($callback,'Downloading admin2codes',0,1);
        $url = self::getUrl('admin2Codes.txt');
        $req = new HttpRequest($url);
        
        $reqs = explode("\n", $req->getResponse());
        $rows = 0; $ltime = 0;
        foreach($reqs as $reql) {
            $rd = explode("\t",trim($reql));
            if (count($rd)>1) {
                $db->updateRow(
                    "REPLACE INTO geonames_admin2codes ".
                    "(admin2code,name,longname,geoid) VALUES (%s,%s,%s,%d)",
                    $rd[0],$rd[1],$rd[2],$rd[3]);
                if (microtime(true) > $ltime+1) {
                    cb($callback,'Importing admin2codes ... '.$rows." records imported", 1);
                    $ltime = microtime(true);
                }
                $rows++;
            }
        }
        cb($callback,"Imported admin2codes (".$rows." rows)");
        
    }

	private static function updateSets(callback $callback = null) {

		// Update the callback if one is present
		if ($callback) $callback->call('Updating sets...');
		// Pull the country list
		$db = new DatabaseConnection();
		$rs = $db->getRows("SELECT isocode,capital FROM geonames_countryinfo");
		foreach($rs as $cc) {
			$f = $db->getSingleRow("SELECT * FROM geonames_datasets WHERE setkey=%s", $cc['isocode']);
			if (!$f) {
				$db->insertRow("INSERT INTO geonames_datasets (setkey,setname,url,active) VALUES (%s,%s,%s,0)",
					$cc['isocode'], $cc['capital'], self::getUrl($cc['isocode'].'.zip'));
			}
		}
		if ($callback) $callback->call('All sets updated');
		
	}

	private static function updateCache(callback $callback = null) {

		$cache = base::expand('app:/cache/geonames/');
		if (!file_exists($cache)) mkdir($cache,0777,true);
		$db = new DatabaseConnection();
		// Pull the list of countries to update
		$rs = $db->getRows("SELECT * FROM geonames_datasets WHERE active=1");
		foreach($rs as $ci) {
            $download = false;
			if (!file_exists($cache.$ci['setkey'].'.gz')) {
                $download = true;
			} else {
				$file = new HttpRequest($ci['url'], $cache.$ci['setkey'].'.zip', array(
                     'method' => 'head'
                ));
                $hdr = $file->headers();
                $etaglocal = file_get_contents($cache.$ci['setkey'].'.etag');
                $etagremote = $hdr['ETag'];
                if ($etaglocal != $etagremote) {
                    $download = true;
                }
            }
            if ($download) {
				cb($callback,'Downloading '.$ci['url'],0,1);
				$file = new HttpDownload($ci['url'], $cache.$ci['setkey'].'.zip');
                $hdr = $file->headers();
                file_put_contents($cache.$ci['setkey'].'.etag', $hdr['ETag']);
				cb($callback,'Recompressing '.$ci['setkey'].'.gz',0,1);
				// Open input stream
				$fin = fopen('zip://'.$cache.$ci['setkey'].'.zip#'.$ci['setkey'].'.txt','r');
				$fout = fopen('compress.zlib://'.$cache.$ci['setkey'].'.gz','w');
				stream_copy_to_stream($fin,$fout);
                fclose($fin);
                fclose($fout);
				cb($callback,'Saved '.$ci['setkey'].'.gz');
            }
		}
        
        // Update hierarchy
        $url = self::getUrl('hierarchy.zip');
        if (file_exists($cache.'hierarchy.etag')) {
            $hetag = file_get_contents($cache.'hierarchy.etag');
        } else {
            $hetag = null;
        }
        cb($callback,'Updating hierarchy...',0,1);
        $head = new HttpRequest($url, array(
            'method' => 'head'
        ));
        $headers = $head->headers();
        if ($hetag != $headers['ETag']) {
            file_put_contents($cache.'hierarchy.etag', $headers['ETag']);
            $file = new HttpDownload($url, $cache.'hierarchy.zip');
        }
        // Open input stream
        $fin = fopen('zip://'.$cache.'hierarchy.zip#hierarchy.txt','r');
        $fout = fopen('compress.zlib://'.$cache.'hierarchy.gz','w');
        stream_copy_to_stream($fin,$fout);
        fclose($fin);
        fclose($fout);
        cb($callback,'Saved hierarchy...');

	}

	public static function updateActiveCountries(callback $callback = null) {

		self::updateCache($callback);
        
		$db = new DatabaseConnection();
		$cache = base::expand('app:/cache/geonames/');
        
        // Update hierarchy
        cb($callback,'Importing hierarchy ...',1);
        $fin = fopen('compress.zlib://'.$cache.'hierarchy.gz','r');
        $rows = 0;
        $ltime = 0;
        while (!feof($fin)) {
            $fd = trim(fgets($fin));
            $ds = explode("\t", $fd."\t\t");
            $db->updateRow("REPLACE INTO geonames_hierarchy ".
                    "(parentid,geoid,htype) ".
                    "VALUES ".
                    "(%d,%d,%s)", $ds[0],$ds[1],$ds[2]);
            if (microtime(true) > $ltime+1) {
                cb($callback,'Importing hierarchy ... '.$rows." records imported", 1);
                $ltime = microtime(true);
            }
            $rows++;
        }
        cb($callback,'Imported hierarchy ('.$rows.' records)');
        fclose($fin);
        
		// Pull the list of countries to import
		$rs = $db->getRows("SELECT * FROM geonames_datasets WHERE active=1");
		foreach($rs as $ci) {
			cb($callback,'Importing '.$ci['setkey'].' ...', 1);
			$fin = fopen('compress.zlib://'.$cache.$ci['setkey'].'.gz','r');
            $rows = 0;
            $ltime = 0;
            while(!feof($fin)) {
				$dl = fgets($fin);
				if (trim($dl) != '') {
					$ds = explode("\t",$dl);
					$db->updateRow(
						'REPLACE INTO geonames '.
						'(geoid,name,asciiname,alternatenames,'.
						'latitude,longitude,featureclass,featurecode,'.
						'countrycode,countrycodealt,admin1code,admin2code,'.
						'admin3code,admin4code,population,elevation,'.
						'gtopo30,timezone,modificationdate) '.
						'VALUES '.
						'(%d,%s,%s,%s, %.5f,%.5f,%s,%s,'.
						'%s,%s,%s,%s, %s,%s,%d,%d,'.
						'%d,%s,%s)',
						$ds[0], $ds[1], $ds[2], $ds[3],
						$ds[4], $ds[5], $ds[6], $ds[7],
						$ds[8], $ds[9], $ds[10], $ds[11],
						$ds[12], $ds[13], $ds[14], $ds[15],
						$ds[16], $ds[17], $ds[18]);
				}
                if (microtime(true) > $ltime+1) {
        			cb($callback,'Importing '.$ci['setkey'].' ... '.$rows." records imported", 1);
                    $ltime = microtime(true);
                }
                $rows++;
			}
            fclose($fin);
			cb($callback,'Imported '.$ci['setkey']. " (".$rows." records)");
		}


	}

	public static function setCountryStatus($country,$enabled=true) {

		$db = new DatabaseConnection();
		$db->updateRow("UPDATE geonames_datasets SET active=%d WHERE setkey=%s", ($enabled)?1:0, $country);

	}

}

abstract class GeoCountry {

    function getInfoFromCountryCode($cc) {

    }

    function getInfoFromCountryName($cn) {

    }

}

// using('ldwp.action');

/**
 * Lepton Worker for installing of geo country database
 */

class GeoCountryAction {

    const PSTATE_INITIALIZE = NULL;
    const PSTATE_DOWNLOAD = 1;
    const PSTATE_IMPORT = 2;
    const PSTATE_FINALIZE = 3;

    /**
     * Process will be called continuously until the worker signalize its completion
     * by setting $worker->complete = true;
     */
    function process(WorkerState $worker, ActionState $action) {
        // Process based on the actions process state. It starts empty as NULL,
        // which prepares the actual process.
        switch($action->pstate) {
        case self::PSTATE_INITIALIZE:
            // Start processing here, check tables etc,
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_DOWNLOAD;
            break;
        case self::PSTATE_DOWNLOAD:
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_IMPORT;
            break;
        case self::PSTATE_IMPORT:
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_FINALIZE;
            break;
        case self::PSTATE_FINALIZE:
            // Flag the job as completed
            $worker->complete = true;
            break;
        }
        $this->yield();
    }
}

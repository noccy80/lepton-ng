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
					'capital,area,population,continent,'.
					'tld,currencycode,currencyname,phone,'.
					'postalcode,postalcoderegex,languages,geoid,'.
					'neighbours,equivalentfips) '.
					'VALUES '.
					'(%s,%s,%s,%s, %s,%d,%d,%s, '.
					'%s,%s,%s,%s, %s,%s,%s,%d, '.
					'%s,%s)',
					$cols[0], $cols[1], $cols[2], $cols[3],
					$cols[4], $cols[5], $cols[6], $cols[7],
					$cols[8], $cols[9], $cols[10], $cols[11],
					$cols[12], $cols[13], $cols[14], $cols[15],
					$cols[16], $cols[17]
				);
			}
		} // end foreach

		if ($callback) $callback->call('Country info imported OK');
		self::updateSets($callback);

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
			if (!file_exists($cache.$ci['setkey'].'.gz')) {
				cb($callback,'Downloading '.$ci['url'],0,1);
				$file = new HttpDownload($ci['url'], $cache.$ci['setkey'].'.zip');
				cb($callback,'Recompressing '.$ci['setkey'].'.gz',0,1);
				// Open input stream
				$fin = fopen('zip://'.$cache.$ci['setkey'].'.zip#'.$ci['setkey'].'.txt','r');
				$fout = fopen('compress.zlib://'.$cache.$ci['setkey'].'.gz','w');
				stream_copy_to_stream($fin,$fout);
				cb($callback,'Saved '.$ci['setkey'].'.gz');
			}
		}

	}

	public static function updateActiveCountries(callback $callback = null) {

		self::updateCache($callback);

		$db = new DatabaseConnection();
		$cache = base::expand('app:/cache/geonames/');
		// Pull the list of countries to import
		$rs = $db->getRows("SELECT * FROM geonames_datasets WHERE active=1");
		foreach($rs as $ci) {
			cb($callback,'Importing '.$ci['setkey'].' ...', 1);
			$fin = fopen('compress.zlib://'.$cache.$ci['setkey'].'.gz','r');
			while(!feof($fin)) {
				$dl = fgets($fin);
				if (trim($dl) != '') {
					$ds = explode("\t",$dl);
					$db->updateRow(
						'REPLACE INTO geonames '.
						'(geonameid,name,asciiname,alternatenames,'.
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
			}
			cb($callback,'Imported '.$ci['setkey']);
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

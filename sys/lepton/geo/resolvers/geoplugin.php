<?php

using('lepton.geo.geo');

/**
 * 
 * 
 * @todo Make this class extendable with various APIs including maxmind's
 *       geoIp.
 * 
 * 'geoplugin_city' => 'Hammarö',
 * 'geoplugin_region' => 'Värmlands Län',
 * 'geoplugin_areaCode' => '0',
 * 'geoplugin_dmaCode' => '0',
 * 'geoplugin_countryCode' => 'SE',
 * 'geoplugin_countryName' => 'Sweden',
 * 'geoplugin_continentCode' => 'EU',
 * 'geoplugin_latitude' => '59.333301544189',
 * 'geoplugin_longitude' => '13.516699790955',
 * 'geoplugin_regionCode' => '22',
 * 'geoplugin_regionName' => 'Värmlands Län',
 * 'geoplugin_currencyCode' => 'SEK',
 * 'geoplugin_currencySymbol' => 'kr',
 * 'geoplugin_currencyConverter' => 6.2286000252,
 */
class GeopluginResolver {

	const API_QUERY_URL = 'http://www.geoplugin.net/php.gp?ip=%s';

	static function getInformationFromIp($ip) {

		$data = unserialize(file_get_contents(sprintf(self::API_QUERY_URL, $ip)));
		foreach($data as $k=>$v) {
			$ret[str_replace('geoplugin_','geo:',strtolower($k))] = $v;
		}
		return $ret;

	}

}

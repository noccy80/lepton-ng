<?php

config::def(GeoLocation::KEY_RESOLVER,'GeopluginResolver');

interface IGeoResolver {
	static function getInformationFromIp($ip);
}

abstract class GeoResolver implements IGeoResolver {
}

using('lepton.geo.resolvers.*');

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
class GeoLocation {

	const API_QUERY_URL = 'http://www.geoplugin.net/php.gp?ip=%s';
	const KEY_RESOLVER = 'lepton.geo.defaultresolver';

	static function getPositionFromIp($ip) {

		$geodata = self::getInformationFromIp($ip);
		if ($geodata) {
			$pos = new GeoPosition(
				$geodata['geo:latitude'],
				$geodata['geo:longitude']
			);
			return $pos;
		}
		return null;

	}

	static function getInformationFromIp($ip) {

		if (!$ip) throw new BadArgumentException("Must specify an IP");
		return call_user_func_array(array(config(self::KEY_RESOLVER),'getInformationFromIp'), array($ip));

	}

}

class GeoPosition {

	function __construct($lat,$lon) {
		$this->lat = floatval($lat);
		$this->lon = floatval($lon);
	}

	function getDistance(GeoPosition $target) {
		return Geo::getDistance($this,$target);
	}

	public function __get($key) {
		switch($key) {
			case 'lat':
			case 'latitude':
				return $this->lat;
				break;
			case 'lon':
			case 'longitude':
				return $this->lon;
				break;
			default:
				throw new BadPropertyException("No such property ".$key." in ".__CLASS__);
		}
	}

	public function __set($key,$value) {
		switch($key) {
			case 'lat':
			case 'latitude':
				$this->lat = floatval($value);
				break;
			case 'lon':
			case 'longitude':
				$this->lon = floatval($value);
				break;
			default:
				throw new BadPropertyException("No such property ".$key." in ".__CLASS__);
		}
	}

}

class GeoUtil {

	const EARTH_RADIUS_METER = 6378100; // radius of earth in meters

	static function getDistance(GeoPosition $pos1, GeoPosition $pos2) {

		$latDist = $pos1->lat - $pos2->lat;
		$lngDist = $pos1->lon - $pos2->lon;
		$latDistRad = deg2rad($latDist);
		$lngDistRad = deg2rad($lngDist);
		$sinLatD = sin($latDistRad);
		$sinLngD = sin($lngDistRad);
		$cosLat1 = cos(deg2rad($pos1->lat));
		$cosLat2 = cos(deg2rad($pos2->lat));
		$a = $sinLatD*$sinLatD + $cosLat1*$cosLat2*$sinLngD*$sinLngD*$sinLngD;
		if($a<0) $a = -1*$a;
		$c = 2*atan2(sqrt($a), sqrt(1-$a));
		$distance = self::EARTH_RADIUS_METER * $c;

		return $distance;

	}

}

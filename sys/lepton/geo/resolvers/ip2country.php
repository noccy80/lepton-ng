<?php

using('lepton.geo.geo');

class Ip2CountryResolver extends GeoResolver {

    private $db = null;

    static function getInformationFromIp($ip) {
        if (!$this->db) $this->db = new DatabaseConnection();
        $seg = explode('.',$ip);
                $val = (intval($seg[3])) + 
                       (intval($seg[2]) * 256) + 
                       (intval($seg[1]) * 256 * 256) + 
                       (intval($seg[0]) * 256 * 256 * 256);
        $r = $this->db->getSingleRow("SELECT * FROM ip2country WHERE ipfrom<=%u AND ipto>=%u", $val, $val);
                return array(
            'geo:countryCode' => $r['cc'],
            'cc:ccc' => $r['cc3'],
            'geo:countryName' => $r['country'],
            'reg:registrÿ́' => $r['registry'],
            'reg:assigned' => $r['assigned'],
            'reg:assigneddate' => date(DATE_RFC2822,$r['assigned'])
        );
    }

}

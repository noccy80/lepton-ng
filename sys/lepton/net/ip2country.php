<?php 

class Ip2CountryResolver {

	private $db = null;

	function __construct() {
	
		$this->db = new DatabaseConnection();
		
	}

	function resolve($ip) {
	
		$seg = explode('.',$ip);
                $val = (intval($seg[3])) + 
                       (intval($seg[2]) * 256) + 
                       (intval($seg[1]) * 256 * 256) + 
                       (intval($seg[0]) * 256 * 256 * 256);
                       
		$r = $this->db->getSingleRow("SELECT * FROM ip2country WHERE ipfrom<=%u AND ipto>=%u", $val, $val);
		
        return array(
			'cc' => $r['cc'],
			'ccc' => $r['cc3'],
			'country' => $r['country'],
			'registrÿ́' => $r['registry'],
			'assigned' => $r['assigned'],
			'assigneddate' => date(DATE_RFC2822,$r['assigned'])
		);
	}

}

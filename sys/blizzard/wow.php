<?php

using('lepton.net.httprequest');
using('lepton.web.json');

abstract class ArmoryQuery {
	function getAuthorizationHeader($verb,$url) {
		// UrlPath = <HTTP-Request-URI, from the port to the query string>
		$str = join("\n", array($verb, $date, $url));
		$h = new Hash('sha1');
		$sig = base64($h->hmac(str,$privkey));
		$header = 'BNET '.$pubkey.':'.$sig;
	}
}

/**
 * @class WowApiQuery
 * @brief Query the Blizzard WOW API
 *
 * The specifications for the API can be found at http://wowapi.info and are
 * implemented using standard JSON queries.
 *
 * @license GNU General Public License Verison 3
 * @author Christopher Vagnetoft <noccy.com>
 */
class WowQuery {
	const CHAR_ITEMS = 0x0001; // equipped items
	const CHAR_STATS = 0x0002; // stats
	const CHAR_REPUTATION = 0x0004; // reputation
	const CHAR_SKILLS = 0x0008; // primary and secondary skills
	const CHAR_ACHIEVEMENTS = 0x0010; // achievements/statistics
	const CHAR_TALENTS = 0x0020; // talents
	const CHAR_TITLES = 0x0040; // titles
	const CHAR_MOUNTS = 0x0080; // collected mounts and companions
	const CHAR_QUESTS = 0x0100; // quests
	const CHAR_RECIPES = 0x0200; // profession recipes
	const CHAR_HUNTERPETS = 0x0400; // Hunter pets
	const CHAR_PVP = 0x0800; // PvP information
	const CHAR_ALL = 0xFFFF; // All information

	const GUILD_ROSTER = 0x0001; // members (roster)
	const GUILD_ACHIEVEMENTS = 0x0002; // achievements

	const ARENA_ROSTER = 0x0001; // members (roster)

	/**
	 * @brief Constructor
	 *
	 * @param String $region The region to query, such as "eu" or "us"
	 */
	function __construct($region) {

		// TODO: Determine if we have caching active and if so enable it for the API queries
		$this->region = $region;
		
	}

	/**
	 * @brief Retrieve the status of one or all realms
	 *
	 * @param String $realm The realm to query, can be null.
	 * @return Array The array of realms and their status
	 */
	public function getRealmStatus($realm = null) {

		$url = sprintf('http://%s.battle.net/api/wow/realm/status', $this->region);
		if ($realm != null)
			$url.= '?realm=' . $realm;
		$request = new HttpRequest($url);
		$ret = json::decode((string)$request);

		return $ret;
		
	}

	/**
	 * @brief Retrieve a specific character
	 *
	 * @param String $realm The realm to query.
	 * @param String $character The character to query
	 * @param Int $fields The fields to retrieve
	 * @return Array The character or null
	 */
	public function getCharacter($realm, $character, $fields=null) {

		// ?fields=...,...,...
		// Basic information: name, level, class, race, gender, faction, guild, achievement points
		// Optional fields: equipped items, stats, reputation, primary and secondary skills
		// achievements/statistics, talents, titles, collected mounts and companions,
		// quests, profession recipes, Hunter pets, PvP information
		
		$fieldsarr = array();
		if ($fields & self::CHAR_ITEMS)
			$fieldsarr[] = 'items';
		if ($fields & self::CHAR_STATS)
			$fieldsarr[] = 'stats';
		if ($fields & self::CHAR_REPUTATION)
			$fieldsarr[] = 'reputation';
		if ($fields & self::CHAR_SKILLS)
			$fieldsarr[] = 'skills'; // <- incorrect
		if ($fields & self::CHAR_ACHIEVEMENTS)
			$fieldsarr[] = 'achievements';
		if ($fields & self::CHAR_TALENTS)
			$fieldsarr[] = 'talents';
		if ($fields & self::CHAR_TITLES)
			$fieldsarr[] = 'titles';
		if ($fields & self::CHAR_MOUNTS)
			$fieldsarr[] = 'mounts';
		if ($fields & self::CHAR_QUESTS)
			$fieldsarr[] = 'quests'; // <- incorrect
		if ($fields & self::CHAR_RECIPES)
			$fieldsarr[] = 'recipes'; // <- incorrect
		if ($fields & self::CHAR_HUNTERPETS)
			$fieldsarr[] = 'hunterpets'; // <- incorrect
		if ($fields & self::CHAR_PVP)
			$fieldsarr[] = 'pvpinformation'; // <- incorrect

		if (count($fields) > 0) {
			$fieldstr = '?fields=' . join(',', $fieldsarr);
		} else {
			$fieldstr = '';
		}

		$url = sprintf('http://%s.battle.net/api/wow/character/%s/%s%s', $this->region, $realm, $character, $fieldstr);
		$request = new HttpRequest($url);
		$ret = json::decode((string)$request);

		$char = new WowCharacter($this->region, $ret);
		return $char;
		
	}

	public function getGuild($realm, $guildname, $fields=null) {

		// URL: /api/wow/guild/{realm}/{name}
		// Basic information: name, level, achievement points
		// Optional fields: members (roster), achievements

		$fieldsarr = array();
		if ($fields & self::GUILD_ROSTER)
			$fieldsarr[] = 'members';
		if ($fields & self::GUILD_ACHIEVEMENTS)
			$fieldsarr[] = 'achievements';

		if (count($fields) > 0) {
			$fieldstr = '?fields=' . join(',', $fieldsarr);
		} else {
			$fieldstr = '';
		}

		$url = sprintf('http://%s.battle.net/api/wow/guild/%s/%s%s', $this->region, $realm, $guildname, $fieldstr);
		$request = new HttpRequest($url);
		$ret = json::decode((string)$request);
		return $ret;
		
	}

	public function getArenaTeam($realm, $teamname, $size, $fields=null) {

		// URL: /api/wow/arena/{realm}/{size}/{name} (size being 2v2, 3v3 or 5v5)
		// Basic information: name, ranking, rating, weekly/season statistics
		// Optional fields: members (roster)

		$url = sprintf('http://%s.battle.net/api/wow/arena/%s/%s/%s', $this->region, $realm, $size, $teamname);
		$request = new HttpRequest($url);
		$ret = json::decode((string) $request);
		return $ret;
		
	}

	public function classIdToString($id) {
		return WowCharacter::className($id);
	}

	public function raceIdToString($id) {
		return WowCharacter::raceName($id);
	}
	
	public function genderIdToString($id) {
		
		switch ($id) {
			case 1: return "Female";
			case 0: return "Male";
			default: return sprintf("Gender[#%d]", $id);
		}
		
	}

}

class WowCharacter {

	const CLASS_DEATHKNIGHT = 6;
	const CLASS_DRUID = 11;
	const CLASS_HUNTER = 3;
	const CLASS_MAGE = 8;
	const CLASS_PALADIN = 2;
	const CLASS_PRIEST = 5;
	const CLASS_ROGUE = 4;
	const CLASS_SHAMAN = 7;
	const CLASS_WARLOCK = 9;
	const CLASS_WARRIOR = 1;
	
	private $_region;
	private $_data;
	private $_thumbnail;

	function __construct($region, $data) {

		$this->_region = $region;
		$this->_data = (array)$data;
		if (count($this->_data) == 0) throw new BaseException("Bad character data");

		$this->_thumbnail = $this->_data['thumbnail'];
	}
	
	function __get($key) {
		switch($key) {
			case 'genderstr': 
				return WowQuery::genderIdToString($this->_data['gender']);
			case 'classstr': 
				return WowQuery::classIdToString($this->_data['class']);
			case 'racestr': 
				return WowQuery::raceIdToString($this->_data['race']);
			default:
				if (arr::hasKey($this->_data,$key)) 
					return $this->_data[$key];
				throw new WowException("No such key in character: ".$key);
		}
		return null;
	}
	
	function __sleep() {
		return array_keys(get_object_vars($this));
	}

	function getThumbnailSrc() {
		return sprintf('http://%s.battle.net/static-render/%s/%s', $this->_region, $this->_region, $this->_thumbnail);
	}
	
	static function thumbnail($region,$surl) {
		return sprintf('http://%s.battle.net/static-render/%s/%s', $region, $region, $surl);
	}
	
	static function classColor($classid) {
		switch($classid) {
			case self::CLASS_DEATHKNIGHT: return '#C41F3B';
			case self::CLASS_DRUID: return '#FF7D0A';
			case self::CLASS_HUNTER: return '#ABD473';
			case self::CLASS_MAGE: return '#69CCF0';
			case self::CLASS_PALADIN: return '#F58CBA';
			case self::CLASS_PRIEST: return '#FFFFFF';
			case self::CLASS_ROGUE: return '#FFF569';
			case self::CLASS_SHAMAN: return '#0070DE';
			case self::CLASS_WARLOCK: return '#9482C9';
			case self::CLASS_WARRIOR: return '#C79C6E';
		}
	}

	static function className($classid) {
		switch ($classid) {
			case 1: return "Warrior";
			case 2: return "Paladin";
			case 3: return "Hunter";
			case 4: return "Rogue";
			case 5: return "Priest";
			case 6: return "DeathKnight";
			case 7: return "Shaman";
			case 8: return "Mage";
			case 9: return "Warlock";
			case 11: return "Druid";
			default: return sprintf("Class[#%d]", $id);
		}
	}

	static function raceName($raceid) {
		switch ($raceid) {
			case 1: return "Human";
			case 3: return "Dwarf";
			case 4: return "Night Elf";
			case 7: return "Gnome";
			case 11: return "Draenei";
			case 22: return "Worgen";
			default: return sprintf("Race[#%d]", $id);
		}
	}
	
}

class WowItem {

	private $_region;
	private $_icon;

	/**
	 * @brief Retrieve the image URL of the item
	 *
	 * @param Integer $size Size in pixels, one of 18,36 or 56
	 * @return String The url of the image
	 */
	static function getImageSrc($size=56) {

		return sprintf('http://%s.media.blizzard.com/wow/icons/%d/%s', $this->_region, $size, $this->_icon);

	}

}

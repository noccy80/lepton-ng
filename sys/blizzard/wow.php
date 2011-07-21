<?php

using('lepton.net.httprequest');

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
class WowApiQuery {

	const CHAR_ITEMS         = 0x0001; // equipped items
	const CHAR_STATS         = 0x0002; // stats
	const CHAR_REPUTATION    = 0x0004; // reputation
	const CHAR_SKILLS        = 0x0008; // primary and secondary skills
	const CHAR_ACHIEVEMENTS  = 0x0010; // achievements/statistics
	const CHAR_TALENTS       = 0x0020; // talents
	const CHAR_TITLES        = 0x0040; // titles
	const CHAR_MOUNTS        = 0x0080; // collected mounts and companions
	const CHAR_QUESTS        = 0x0100; // quests
	const CHAR_RECIPES       = 0x0200; // profession recipes
	const CHAR_HUNTERPETS    = 0x0400; // Hunter pets
	const CHAR_PVP           = 0x0800; // PvP information
	const CHAR_ALL           = 0xFFFF; // All information

    const GUILD_ROSTER       = 0x0001; // members (roster)
    const GUILD_ACHIEVEMENTS = 0x0002; // achievements

    const ARENA_ROSTER       = 0x0001; // members (roster)
    
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
		if ($realm != null) $url.= '?realm='.$realm;
		$request = new HttpRequest($url);
		$ret = json_decode((string)$request);

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
	public function getCharacter($realm,$character,$fields=null) {

		// ?fields=...,...,...
		// Basic information: name, level, class, race, gender, faction, guild, achievement points
		// Optional fields: equipped items, stats, reputation, primary and secondary skills, achievements/statistics, talents, titles, collected mounts and companions, quests, profession recipes, Hunter pets, PvP information

		$url = sprintf('http://%s.battle.net/api/wow/character/%s/%s', $this->region, $realm, $character);
		$request = new HttpRequest($url);
   		$ret = json_decode((string)$request);
		return $ret;

	}

    public function getGuild($realm,$guildname,$fields=null) {
        
        // URL: /api/wow/guild/{realm}/{name}
        // Basic information: name, level, achievement points
        // Optional fields: members (roster), achievements

		$url = sprintf('http://%s.battle.net/api/wow/guild/%s/%s', $this->region, $realm, $guildname);
		$request = new HttpRequest($url);
   		$ret = json_decode((string)$request);
		return $ret;
    }

    public function getArenaTeam($realm,$teamname,$size,$fields=null) {
        
        // URL: /api/wow/arena/{realm}/{size}/{name} (size being 2v2, 3v3 or 5v5)
        // Basic information: name, ranking, rating, weekly/season statistics
        // Optional fields: members (roster)

		$url = sprintf('http://%s.battle.net/api/wow/arena/%s/%s/%s', $this->region, $realm, $size, $teamname);
		$request = new HttpRequest($url);
   		$ret = json_decode((string)$request);
		return $ret;
    }

}


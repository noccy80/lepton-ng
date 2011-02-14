<?php

/**
 * @brief Forex-based Currency Conversion
 *
 * The service used for this data requires attribution. See
 * http://rss.timegenie.com/foreign_exchange_rates_forex
 *
 * Example:
 *   $r = new CurrencyExchange();
 *   $r->update(); // Only needed once per day
 *   $r->convert(10,'GBP','SEK'); // 10 UK Pounds in SEK
 *
 * @license GNU GPL v3
 * @license CC-BY (Dataset)
 */

class CurrencyException extends Exception { }

class CurrencyExchange {

    private $db = null;
    private $url = 'http://rss.timegenie.com/forex.txt';

    function __construct() {

        // Create db connection
        $this->db = new DatabaseConnection();
        $this->createTables();

    }

    function createTables() {

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS currencyexchange (symbol CHAR(3) NOT NULL PRIMARY KEY, name VARCHAR(32), rate DECIMAL(20,10));"
        );

    }

    /**
     * @brief Update the symbols in the database
     *
     */
    function update() {

        $this->createTables();
        $upd = fopen($this->url,'r');
        if (!$upd) throw new BaseException("Opening of stream failed.");
        while (!feof($upd)) {
            $d = fgetcsv($upd,1024,'|');
            $this->db->updateRow("REPLACE INTO currencyexchange (symbol,name,rate) VALUES (%s,%s,%20.10f)", $d[0], $d[1], floatval($d[2]));
        }

    }

    /**
     * @brief Convert one currency to another
     *
     * @todo Check that the symbols exist
     *
     * @param Float $fromval The amount to convert
     * @param String $fromcur The source currency symbol
     * @param String $tocur The destination currency symbol
     * @return Float The amount in the destination currency
     */
    function convert($fromval,$fromcur,$tocur) {

        // The dataset is based on euros
        $reur = $this->db->getSingleRow("SELECT * FROM currencyexchange WHERE symbol=%s",'EUR');
        $rfrom = $this->db->getSingleRow("SELECT * FROM currencyexchange WHERE symbol=%s",$fromcur);
        $rto = $this->db->getSingleRow("SELECT * FROM currencyexchange WHERE symbol=%s",$tocur);

        if (!$rfrom) throw new CurrencyException("Invalid source currency ".$fromcur);
        if (!$rto) throw new CurrencyException("Invalid destination currency".$tocur);
        return ($fromval / (floatval($rfrom['rate']) * floatval($reur['rate']))) * floatval($rto['rate']);

    }

}

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

class CurrencyException extends BaseException { }

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

class CurrencyAmount {
    
    private $symbol = null;
    private $amount = null;
    
    function __construct($amount,$currency=null) {
        
        // Try to determine if a symbol is present in the string
        $stramount = str_replace(' ','',strval($amount));
        
        // Explode on boundaries between characters and digits
        $last = null; $out = array(); $buffer = '';
        for($n = 0; $n < strlen($stramount)+1; $n++) {
            if ($n < strlen($stramount)) {
                $char = $stramount[$n];
                $curr = is_numeric($char);
                if (strpos(',.',$char)!==false) $curr = true;
            } else {
                $char = null;
            }
            if ($last !== null) {
                if ($last != $curr) {
                    $out[] = $buffer; $buffer = '';
                }
            }
            if ($char != null) $buffer.= strval($char);
            $last = $curr;
        }
        if ($buffer != '') $out[] = $buffer;

        // $out now contains the different chunks of numeric and non-numeric
        // data. Search for the various text chunks in order to match it with
        // the currency exchange table.
        $db = new DatabaseConnection();
        foreach($out as $symstr) {
            if (!is_numeric($symstr)) {
                $sym = $db->getSingleRow("SELECT * FROM currencyexchange WHERE symbol=%s",$symstr);
                if ($sym) {
                    $this->symbol = $symstr;
                }
            } else {
                $this->amount = floatval($symstr);
            }
        }
        
    }

    function __toString() {
        return sprintf('%s %.2f', $this->symbol, $this->amount);
    }
    
    function convert($currency) {
        $cc = new CurrencyExchange();
        return sprintf('%s %.2f', $currency, $cc->convert($this->amount, $this->symbol, $currency));
    }
    
}
<?php

function getGoogleTranslation($sString, $bEscapeParams = true)
{
    // "escape" sprintf paramerters
    if ($bEscapeParams)
    {
        $sPatern = '/(?:%%|%(?:[0-9]+\$)?[+-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeufFosxX])/';        
        $sEscapeString = '<span class="notranslate">$0</span>';
        $sString = preg_replace($sPatern, $sEscapeString, $sString);
    }

    // Compose data array (English to Dutch)
    $aData = array(
        'v'            => '1.0',
        'q'            => $sString,
        'langpair'    => 'en|nl',
    );

    // Initialize connection
    $rService = curl_init();
    
    // Connection settings
    curl_setopt($rService, CURLOPT_URL, 'http://ajax.googleapis.com/ajax/services/language/translate');
    curl_setopt($rService, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($rService, CURLOPT_POSTFIELDS, $aData);
    
    // Execute request
    $sResponse = curl_exec($rService);

    // Close connection
    curl_close($rService);
    
    // Extract text from JSON response
    $oResponse = json_decode($sResponse);
    if (isset($oResponse->responseData->translatedText))
    {
        $sTranslation = $oResponse->responseData->translatedText;
    }
    else
    {
        // If some error occured, use the original string
        $sTranslation = $sString;
    }
    
    // Replace "notranslate" tags
    if ($bEscapeParams)
    {
        $sEscapePatern = '/<span class="notranslate">([^<]*)<\/span>/';
        $sTranslation = preg_replace($sEscapePatern, '$1', $sTranslation);
    }
    
    // Return result
    return $sTranslation;
}

?>

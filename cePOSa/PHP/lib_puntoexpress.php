<?php
class puntoexpress
{
    public $verbose = false;
    public $desarollo = true;  
    
    public function enviar ($DOM_ID, $idRef, $amount, $email, $phone)
    {
        $amount = number_format($amount,2,'','');
        $DATA['idTrx'] = '123';
        $DATA['idRef'] = $idRef;
        $DATA['idSeller'] = '3323';
        $DATA['currency'] = 'USD';
        $DATA['amount'] = $amount;
        $DATA['email'] = $email;
        $DATA['validity'] = '5';
        $DATA['fieldsAdded'] = '';
        $DATA['dateTrx'] = date('Ymd H:i:s');
        $DATA['urlRedirect'] = '';
        
        if ($this->verbose)
        {
            var_dump($DATA);
            echo '<hr />'.json_encode($DATA).'<hr />';
        }
        
        if ($this->desarrollo)        {
            $puerto = '8888';
        } else {
            $puerto = '8080';
        }
        
        $url = 'http://desa.api-services.puntoxpress.com:' . $puerto . '/PEXTokenServices/api/services/token/request';
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        
        if ($this->verbose){
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'rw+');
            curl_setopt($curl, CURLOPT_STDERR, $verbose);
        }                                                                                       
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($DATA));
        curl_setopt($curl, CURLOPT_REFERER, 'http://beta.flor360.com/TEST/puntoexpress.php');
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/plain"));
    
        $result = curl_exec($curl);
        $resultArray = curl_getinfo($curl);
        
        if ($this->verbose){        
            !rewind($verbose);
            $verboseLog = stream_get_contents($verbose);        
            echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        }
    
        curl_close($curl);
    
        if ($resultArray['http_code'] == 200)
        {
            $doc = new DomDocument;
            @$doc->LoadHTML($result);
            $xpath = new DOMXPath($doc);
            $control = '<div id="'. $DOM_ID .'_control" class="lib_puntoexpress" style="text-align:center; margin:auto; width:660px;">' . $doc->SaveHTML($xpath->query("//*[@id='panelPEX']")->item(0)) . '</div>';
            $impresion = '<div id="'. $DOM_ID .'_impresion" class="lib_puntoexpress" style="text-align:center; margin:auto; width:660px;display:none;">' . $doc->SaveHTML($xpath->query("//*[@id='panelPrintPEX']")->item(0)) . '</div>';
            
            return  array($control, $impresion);
        } else {
            return false;
        }
            
    }
}
?>
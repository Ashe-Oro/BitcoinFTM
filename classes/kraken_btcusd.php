 <?php
include_once("ticker.php");
class KrakenBTCUSD {
    
    const SERVER_TIME_URL = "https://api.kraken.com/0/public/Time";

    public function getTicker() {
        $url = "https://api.kraken.com";
        $version = 0;
        $method = "Ticker";
        //I don't understand where this pair comes from, but do know it is for BTC USD
        $request = array('pair' => 'XXBTZUSD');
        $curl = curl_init();

        // build the POST data string
        $postdata = http_build_query($request, '', '&');
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Kraken PHP API Agent',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true)
        );
        // make request
        curl_setopt($curl, CURLOPT_URL, $url . '/' . $version . '/public/' . $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());
        //curl_exec($curl);
        $result = curl_exec($curl);

        if($result===false){
            echo "CURL error: " . curl_error($curl);
            return false;
        }
        // decode results
        $result = json_decode($result, true);
        if(!is_array($result)){
            echo "JSON decode error";
            return false;
        }

        //get the items in the BTC/USD array
        $result = $result['result']['XXBTZUSD'];

        /* The following if from the Kraken API documentation: https://www.kraken.com/help/api#get-ticker-info
        <pair_name> = pair name
            a = ask array(<price>, <lot volume>),
            b = bid array(<price>, <lot volume>),
            c = last trade closed array(<price>, <lot volume>),
            v = volume array(<today>, <last 24 hours>),
            p = volume weighted average price array(<today>, <last 24 hours>),
            t = number of trades array(<today>, <last 24 hours>),
            l = low array(<today>, <last 24 hours>),
            h = high array(<today>, <last 24 hours>),
            o = today's opening price
        */

        $ticker = new Ticker($result['h'][0], $result['l'][0], $result['c'][0], $this->getServerTime(), $result['b'][0], $result['v'][0], $result['a'][0]);

        return $ticker->getTicker();
    }

    function getServerTime() {
        $json = file_get_contents(self::SERVER_TIME_URL);
        $obj = json_decode($json);
        $timestamp = $obj->{'result'}->{'unixtime'};
        return $timestamp;
    }

}
?> 
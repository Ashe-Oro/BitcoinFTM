<?php
require_once('privatemarket.php');

class PrivateCryptoTradeUSD extends PrivateMarket
{
    const API_URL = "https://crypto-trade.com/";
    const API = "api/";
    const METHOD_GET_INFO = "/private/getinfo/";
    const METHOD_TRADE = "/private/trade/";

    private $apiVersion = 1;
    
    private $ch = NULL;

    public function __construct($clientID, $key, $secret)
    {
        parent::__construct("USD", $clientID, $key, $secret);
        $this->getInfo();
    }
    
    protected function _loadClient($clientID, $key, $secret)
    {
        $this->privatekey = $key;
        $this->secret = $secret;
        $this->clientID = $clientID;
    }

    protected function _sendRequest($url, $params=array(), $method)
    {
        $rUrl = $url . self::API . $this->apiVersion . $method;                
        $response = array();
        
        $response['result'] = 'success';
        $response['return'] = false;
        iLog("[PrivateCryptoTradeUSD] Sending Request: {$rUrl}");
        
        
        if ($method == self::METHOD_TRADE) {
                iLog("[PrivateCryptoTradeUSD] WARNING: Request not sent. Live sell and buy functions currently disabled.");
                return $response; 
        }

        // must have a unique incrementing nonce for every private request
        $params['nonce'] = $this->_createNonce();
        
        // generate the POST data string
        $post_data = http_build_query($params, '', '&');
        
        // set up header
        $headers = array(
                'AuthKey: '.$this->privatekey,
                'AuthSign: '.$this->_getSignature($post_data)
        );
                
        // our curl handle (initialize if required)
        if (is_null($this->ch)){
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; CryptoTrade PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        }
        curl_setopt($this->ch, CURLOPT_URL, $rUrl);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                        
        // run the query
        $res = curl_exec($this->ch);
        if ($res === false) {
            throw new Exception('Could not get reply: ' . curl_error($this->ch));
        }
        $json = json_decode($res);
        if (!$json) {
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        }
        return $json;
    }
        
    protected function _createNonce()
    {
        return round(microtime(true) * 1000);
    }
    
    protected function _getSignature($post_data)
    {
        return hash_hmac('sha512', $post_data, $this->secret);
    }

    protected function _buy($amount, $price)
    {
        iLog("[PrivateCryptoTradeUSD] Create BUY limit order {$amount} @{$price}USD");

        $params = array(
            'pair' => 'btc_usd',
            'type' => 'Buy', 
            'amount' => $amount, 
            'rate' => $price
        );

        try {
            $response = $this->_sendRequest(self::API_URL, $params, self::METHOD_TRADE);
            if ($response){
                if (isset($response['status']) && $response['status'] == "error") {
                    iLog("[PrivateCryptoTradeUSD] ERROR: Buy failed {$response['error']}");
                } else {
                    alert('BUY'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
                    return true;
                }
            }
        } catch (Exception $e) {
            iLog("[PrivateCryptoTradeUSD] ERROR: Buy failed - ".$e->getMessage());
        }
        return false;
    }

    protected function _sell($amount, $price)
    {        
        iLog("[PrivateCryptoTradeUSD] Create BUY limit order {$amount} @{$price}USD");
        
        $params = array(
            'pair' => 'btc_usd',
            'type' => 'Sell', 
            'amount' => $amount, 
            'rate' => $price
        );

        try {
            $response = $this->_sendRequest(self::API_URL, $params, self::METHOD_TRADE);
            if ($response){
                if (isset($response['status']) && $response['status'] == "error") {
                    iLog("[PrivateCryptoTradeUSD] ERROR: Sell failed {$response['error']}");
                } else {
                    alert('SELL'); // WE NEED TO ADD IN POST SALE LOGIC HERE LATER
                    return true;
                }
            }
        } catch (Exception $e) {
            iLog("[PrivateCryptoTradeUSD] ERROR: Buy failed - ".$e->getMessage());
        }
        return false;
    }

    public function getInfo()
    {
        global $config;
        global $DB;
        
        if ($config['live']) { // LIVE TRADING USES LIVE DATA
            try {
                $response = $this->_sendRequest(self::API_URL, array(), self::METHOD_GET_INFO);

                if($response &&  $response['status'] == "success") {
                    $data = $response['data'];
                    $funds = $data['funds'];
                    
                    $this->btcBalance = $funds['btc'];
                    $this->usdBalance = $funds['usd'];
                    iLog("[PrivateCryptoTradeUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
                    return true;
                }
                else {
                    iLog("[PrivateCryptoTradeUSD] ERROR: Get info failed - {$response}");
                    return false;
                }                        
            } catch (Exceptin $e){
                iLog("[PrivateCryptoTradeUSD] ERROR: Get info failed - ".$e->getMessage());
                return false;                        
            }
        }
        else {
            try {
                $result = $DB->query("SELECT * FROM privatemarkets WHERE marketid = {$this->publicmarketid} AND clientid = {$this->clientId}");
                if ($client = $DB->fetch_array_assoc($result)){
                    $this->btcBalance = (float) ($client['btc'] != NULL ? $client['btc'] : 0);
                    $this->usdBalance = (float) ($client['usd'] != NULL ? $client['usd'] : 0);
                    $this->ltcBalance = (float) ($client['ltc'] != NULL ? $client['ltc'] : 0);
                    $this->eurBalance = (float) ($client['eur'] != NULL ? $client['eur'] : 0);
                    iLog("[PrivateCryptoTradeUSD] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD");
                    return true;
                }
            } catch (Exception $e){
                iLog("[PrivateCryptoTradeUSD] ERROR: Get info failed - ".$e->getMessage());
                return false;
            }
        }
        return false;
    }
}
?>

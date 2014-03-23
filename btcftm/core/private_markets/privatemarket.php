<?php
require_once("./core/utils/transaction.php");
require_once("./core/utils/honeypot.php");

abstract class PrivateMarket
{
	public $name = '';
	public $mname = '';
	public $publicmarketid = 0;
  public $publicmarket = NULL;
	public $currency = '';
  public $commission = 0;

	protected $btcBalance = 0;
	protected $eurBalance = 0;
	protected $usdBalance = 0;
	protected $ltcBalance = 0;

	protected $privatekey = '';
  protected $secret = '';
	
	public $clientId = 0;

	public $fc = NULL; // future currency converter

	public function __construct($currency, $clientID, $key, $secret)
	{
		$this->name = get_class($this);
    $this->currency = $currency;
		$this->mname = str_replace("Private", "", str_replace($this->currency, "", $this->name));
		$this->btcBalance = 0;
		$this->eurBalance = 0;
		$this->usdBalance = 0;
		$this->ltcBalance = 0;
		$this->clientId = $clientID;
		$this->_setPublicMarket();
		$this->_loadClient($clientID, $key, $secret);
	}

  abstract protected function _buyLive($amount, $price, $crypto="BTC", $fiat="USD");
  abstract protected function _sellLive($amount, $price, $crypto="BTC", $fiat="USD");
  abstract protected function _withdrawLive($amount, $currency);
  abstract protected function _depositLive($amount, $currency);
  abstract protected function _getLiveInfo();
  abstract protected function _createNonce();
  abstract protected function _loadClient($clientID, $key, $secret); 
  abstract protected function _sendRequest($url, $params=array(), $extraHeaders=NULL);

  protected function _setPublicMarket()
  {
    global $DB;
    $query = "SELECT * FROM markets WHERE name = '".strtolower($this->mname)."'";
    $res = $DB->query($query);
    if ($res){ 
      $row = $DB->fetch_array_assoc($res);
      $this->publicmarketid = $row['id'];
      $this->commission = $row['commission'];
      /*
      $mFolder = "history_markets";
      $mPrefix = "history" : "";
      $mFile = "./core/public_markets/{$mFolder}/{$mPrefix}".strtolower($market_name)."usd.php";

      if (file_exists($mFile)){
        require_once($mFile);
        try {
          $market_name = "History{$market_name}USD";
          $market = new $market_name();
          $this->publicmarket = $market;
          iLog("[{$this->mname}] public {$market_name} loaded.");
        } catch (Exception $e) {
          iLog("[{$this->mname}] ERROR: Market construct function invalid - {$market_name} - ".$e->getMessage());
        }
      }; 
        */
    }
  }

  public function getBalances()
  {
    return array(
      'btc' => $this->btcBalance,
      'usd' => $this->usdBalance,
      'eur' => $this->eurBalance,
      'ltc' => $this->ltcBalance
    );
  }

  public function getPresaleValue($amount, $price, $action='buy')
  {
    return $amount * $price * ($action == 'buy' ? -1 : 1);
  }

  public function getCommissionValue($amount, $price)
  {
    return $amount * $price * $this->commission;
  }

  public function getHoneypotValue($amount, $price)
  {
    global $config;
    return $amount * $price * $this->commission;
  }

  public function getFinalValue($amount, $price, $action='buy')
  {
    $val = $this->getPresaleValue($amount, $price, $action);
    $com = $this->getCommissionValue($amount, $price);
    $honey = $this->getHoneypotValue($amount, $price);
    return $val - $com - $honey;
  }

  public function getActionValues($amount, $price, $action='buy')
  {
    $val = $this->getPresaleValue($amount, $price);
    $com = $this->getCommissionValue($amount, $price);
    $honey = $this->getHoneypotValue($amount, $price);
    $final = $this->getFinalValue($amount, $price);
    return array(
      "presale" => $val,
      "commission" => $com,
      "honeypot" => $honey,
      "final" => $final
    );
  }

  public function getBalance($currency) {
    switch(strtolower($currency)){
      case 'btc': { return $this->btcBalance; }
      case 'usd': { return $this->usdBalance; }
      case 'eur': { return $this->eurBalance; }
      case 'ltc': { return $this->ltcBalance; }
    }
  }

  protected function _addBalance($amount, $currency) {
    global $DB;
    $currency = strtolower($currency);
    //echo "add: {$amount} {$currency}";
    $bal = max($this->{"{$currency}Balance"} + $amount, 0);
    try {
      $query = "UPDATE privatemarkets SET {$currency} = {$bal} WHERE clientid = {$this->clientId} AND marketid = {$this->publicmarketid}";
      //echo $query;
      $DB->query($query);
      $this->{"{$currency}Balance"} = $bal;
      return true;
    } catch (Exception $e) {
      return false;
    }
    return false;
  }

  protected function _subtractBalance($amount, $currency) {
    global $DB;
    $currency = strtolower($currency);
    //echo "subtract: {$amount} {$currency}";
    $bal = max($this->{"{$currency}Balance"} - $amount, 0);
    try {
     $query = "UPDATE privatemarkets SET {$currency} = {$bal} WHERE clientid = {$this->clientId} AND marketid = {$this->publicmarketid}";
     //echo $query;
     $DB->query($query);
     $this->{"{$currency}Balance"} = $bal;
      return true;
    } catch (Exception $e) {
      return false;
    }
    return false;
  }

  public function getAPISecret()
  {
    return $this->secret;
  }

  public function getAPIKey()
  {
    return $this->privatekey;
  }

	protected function _str()
	{
		$str = "{$this->mname}: [btc_balance: {$this->btc_balance}, eur_balance: {$this->eur_balance}, usd_balance: {$this->usd_balance}]";
		return $str;
	}

	public function buy($amount, $price, $crypto="BTC", $fiat="USD")
	{
    global $config;

		$localPrice = $price;
		//$localPrice = $this->fc->convert($price, 'USD', $this->currency);

		iLog("[{$this->mname}] Buy {$amount}{$crypto} at {$this->name} @{$localPrice}{$fiat}");
    if ($config['live']){
      return $this->_buyLive($amount, $price, $crypto, $fiat);
    } else {
      return $this->_buySim($amount, $price, $crypto, $fiat);
    }
	}

  protected function _buySim($amount, $price, $crypto="BTC", $fiat="USD")
  {
    global $config;
    global $honeypot;

    if ($amount > 0 && $price > 0){
      $buyVal = $amount * $price;
      $valCom = $buyVal * $this->commission;
      $valHoney = $buyVal * $config['honey'];
      $totVal = $buyVal + $valCom + $valHoney;

      $fBal = $this->getBalance($fiat);

      if ($totVal <= $this->getBalance($fiat)){
        if ($config['simdelay']){
          // simulate buy delay here
        } else {
          if ($this->_subtractBalance($totVal, $fiat) && $this->_addBalance($amount, $crypto)){
            if ($honeypot){
              $honeypot->addHoney($this->clientId, $this->publicmarketid, 'buy', $amount, $price, $valHoney);
            }
            $this->recordTransaction("buy", $amount, $price, $crypto, $fiat);
            return true;
          }
        }
      }
    }
    return false;
  }

	public function sell($amount, $price, $crypto="BTC", $fiat="USD")
	{
    global $config;
		$localPrice = $price;
		//$localPrice = $this->fc->convert($price, 'USD', $this->currency);

		iLog("[{$this->mname}] Sell {$amount} BTC at {$this->name} @{$localPrice}{$this->currency}");
    if ($config['live']){
      return $this->_sellLive($amount, $price, $crypto="BTC", $fiat="USD");
    } else {
      return $this->_sellSim($amount, $price);
    }
	}

  protected function _sellSim($amount, $price, $crypto="BTC", $fiat="USD")
  {
    global $config;
    global $honeypot;

    if ($amount > 0 && $price > 0){
      $sellVal = $amount * $price;
      $valCom = $sellVal * $this->commission;
      $valHoney = $sellVal * $config['honey'];
      $totVal = $sellVal - $valCom - $valHoney;

      if ($amount <= $this->getBalance($crypto)){
        if ($config['simdelay']){
          // simulate sell delay here
        } else {
          if ($this->_subtractBalance($amount, $crypto) && $this->_addBalance($totVal, $fiat)){
            if ($honeypot) {
              $honeypot->addHoney($this->clientId, $this->publicmarketid, 'sell', $amount, $price, $valHoney);
            }
            $this->recordTransaction("sell", $amount, $price, $crypto, $fiat);
            return true;
          }
        }
      }
    }
    return false;
  }

	public function deposit($amount, $currency)
	{
		global $config;
    if ($config['live']){
      return $this->_depositLive($amount, $currency);
    } else {
      return $this->_depositSim($amount, $currency);
    }
	}

  protected function _depositSim($amount, $currency)
  {
    global $config;

    if ($amount > 0){
      if ($config['simdelay']){
          // simulate sell delay here
      } else {
        $this->_addBalance($amount, $currency);
        $this->recordTransaction("deposit", $amount, 0, $currency, "USD");
        return true;
      }
    }
    return false;
  }

	public function withdraw($amount, $currency)
	{
		global $config;
    if ($config['live']){
      return $this->_withdrawLive($amount, $currency);
    } else {
      return $this->_withdrawSim($amount, $currency);
    }
	}

  protected function _withdrawSim($amount, $currency)
  {
    global $config;

    if ($amount > 0){
      if ($config['simdelay']){
          // simulate sell delay here
      } else {
        $this->_subtractBalance($amount, $currency);
        $this->recordTransaction("withdraw", $amount, 0, $currency, "USD");
        return true;
      }
    }
    return false;
  }

	public function getInfo()
	{
		global $config;
    if ($config['live']){
      return $this->_getLiveInfo();
    } else {
      return $this->_getSimInfo();
    }
	}

  protected function _getSimInfo()
  {
    global $DB;
    try {
      $result = $DB->query("SELECT * FROM privatemarkets WHERE marketid = {$this->publicmarketid} AND clientid = {$this->clientId}");
      if ($client = $DB->fetch_array_assoc($result)){
        $this->btcBalance = (float) ($client['btc'] != NULL ? $client['btc'] : 0);
        $this->usdBalance = (float) ($client['usd'] != NULL ? $client['usd'] : 0);
        $this->ltcBalance = (float) ($client['ltc'] != NULL ? $client['ltc'] : 0);
        $this->eurBalance = (float) ($client['eur'] != NULL ? $client['eur'] : 0);
        iLog("[{$this->mname}] Get Balance: {$this->btcBalance}BTC, {$this->usdBalance}USD, {$this->ltcBalance}LTC, {$this->eurBalance}EUR");
        return true;
      }
    } catch (Exception $e){
      iLog("[{$this->mname}] ERROR: Get info failed - ".$e->getMessage());
      return false;
    }
    return false;
  }

  public function recordTransaction($type, $volume, $price, $fiat, $crypt)
  {
    global $config;
    try {
      $pre = $volume * $price;
      $com = $this->commission * $pre;
      $honey = $config['honey'] * $pre;
      $final = 0;
      switch ($type){
        case 'buy':
        case 'buy-limit':
          $pre *= -1;
          $final = $pre - $com - $honey;
          break;

        case 'sell':
        case 'sell-limit':
          $final = $pre - $com - $honey;
          break;

        case 'stop':
        case 'stop-limit':
          break;

        case 'withdraw':
          $pre *= -1;
          $com = 0;
          $honey = 0;
          $final = $pre;
          break;

        case 'deposit':
          $com = 0;
          $honey = 0;
          $final = $pre;
          break;
      }

      $t = new Transaction(time(), $this->clientId, $this->publicmarketid, $type, $volume, $price, $pre, $com, $honey, $final, $fiat, $crypt);
      $t->record();
    } catch (Exception $e) {
      iLog("[{$this->mname}] ERROR: Record transaction failed - ".$e->getMessage());
    }
    return false;
  }
	
}

class NotImplementedError extends Exception
{
	public function __construct($message='', $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>
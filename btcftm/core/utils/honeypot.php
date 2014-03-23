<?php
class Honeypot
{
  private $pot = array();
  private $honey = 0;

  public function __construct()
  {
    $this->_initHoneyPot();
  }

  private function _initHoneyPot()
  {
    global $DB;
    $this->honey = 0;
    $res = $DB->query("SELECT * FROM honeypot ORDER BY honeyid ASC");
    while($row = $DB->fetch_array_assoc($res)){
      $this->_addHoneyRow($row);
    }
  }

  private function _addHoneyRow($row)
  {
    if ($row && $row['honey']) {
      array_push($this->pot, $row);
      $this->honey += ($row['honey']);
    }
  }

  public function getPot()
  {
    return $this->pot;
  }

  public function getTotalHoney()
  {
    return $this->honey;
  }

  public function getHoneyByMarket($marketid)
  {
    $mhoney = 0;
    foreach($this->honey as $h){
      if ($h['marketid'] == $marketid) {
        $mhoney += $h['honey'];
      }
    }
    return $mhoney;
  }

  public function getHoneyByClient($cid)
  {
    $choney = 0;
    foreach($this->honey as $h){
      if ($h['clientid'] == $cid) {
        $choney += $h['honey'];
      }
    }
    return $choney;
  }

  public function addHoney($clientid, $marketid, $action, $volume, $price, $honey)
  {
    global $DB;
    global $config;

    $ts = time();
    try {
      $res = $DB->query("INSERT INTO honeypot SET clientid = {$clientid}, marketid = {$marketid}, timestamp = {$ts}, action = '{$action}', volume = {$volume}, price = {$price}, honey = {$honey}, honeyrate = {$config['honey']}");
      $res = $DB->query("SELECT * FROM honeypot WHERE clientid = {$clientid} AND marketid = {$marketid} AND timestamp = {$ts}");
      if ($row = $DB->fetch_array_assoc($res)){
        $this->_addHoneyRow($row);
        return true;
      }
    } catch (Exception $e) {
      iLog("[Honeypot] addHoney failed - ".$e->getMessage());
      return false;
    }
    return false;
  }
}
?>
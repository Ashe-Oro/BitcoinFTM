<?php
require_once("currency.php");

class CurrencyList
{
	private $cList = array();
	
	public function __construct($excludeList=NULL)
	{
		global $DB;
		
		$exclude = "";
		if ($excludeList) {
			$exclude = "WHERE NOT (";
			$i = 0;
			foreach($excludeList as $ex) {
				if ($i > 0) { $exclude .= " OR "; }
				$exclude .= "abbr = '{$ex}'";
				$i++;
			}
			$exclude .= ") ";
		}
		
		$result = $DB->query("SELECT * FROM currencies {$exclude}ORDER BY currency_id ASC");
		iLog("[CurrencyList] Loading currencies from DB...");
		while($row = $DB->fetch_array_assoc($result)){
			iLog("[CurrencyList] Loading currency {$row['abbr']}...");
			try {
				$c = new Currency(0, $row);
				$this->cList[$c->abbr] = $c;
			} catch(Exception $e){
				iLog("[CurrencyList] ERROR: Couldn't add currency - ".$e->getMessage());
			}
		}
	}
	
	public function getCurrency($abbr) {
		if (isset($this->cList[$abbr])){
			return $this->cList[$abbr];
		}
		return NULL;
	}
	
	public function getCurrencyList()
	{
		return $this->cList;
	}

  public function printCurrency($amount, $abbr)
  {
    if ($c = $this->getCurrency($abbr)){
      $c->setAmount($amount);
      return "{$c}";
    }
    return "";
  }
}


?>
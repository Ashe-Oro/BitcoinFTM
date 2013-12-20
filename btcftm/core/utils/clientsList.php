<?php
require_once("clients.php");

class ClientsList
{
	private $cList = array();
	
	public function __construct($idList=NULL)
	{
		global $DB;
		
		if (is_string($idList)){
			$idList = explode(',', $idList);
		}
		if (is_array($idList)){
			foreach($idList as $id){
				iLog("[ClientsList] Loading client {$id}...");
				try {
					$c = new Client($id);
					$this->cList[$c->getID()] = $c;
				} catch(Exception $e){
					iLog("[ClientsList] ERROR: Couldn't add client {$id} - ".$e->getMessage());
				}
			}
		} else {
			$result = $DB->query("SELECT * FROM clients WHERE active = 1 ORDER BY username ASC");
			iLog("[ClientsList] Loading client list from DB...");
			while($row = $DB->fetch_array_assoc($result)){
				iLog("[ClientsList] Loading client {$row['username']}...");
				try {
					$c = new Client($row['username']);
					$this->cList[$c->getID()] = $c;
				} catch(Exception $e){
					iLog("[ClientsList] ERROR: Couldn't add client {$id} - ".$e->getMessage());
				}
			}
		}
		
	}
	
	public function getClient($clientID) {
		if (isset($this->cList[$clientID])){
			return $this->cList[$clientID];
		}
		return NULL;
	}
	
	public function getClientsList()
	{
		return $this->cList;
	}
}
?>
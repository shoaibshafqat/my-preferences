<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsUserPreferences_PRLS {
	
	protected $user;
	protected $core;
	protected $DB;
	protected $prefTypes;
	protected $preferences;
	protected $savedPrefs;
	protected $categortyIDs;
	
	public function __construct($user){
		global $wpdb, $myPrefsCore;
		$this->DB = $wpdb;
		$this->core = $myPrefsCore;
		$this->prefTypes = new MyPrefsTypes_PRLS();
		$this->user = $user;
		$this->load();
	}
	
	protected function getPreferences(){
		if($this->preferences)
			return $this->preferences;
		
		return $this->load();		
	}
	
	public function load(){
		$this->preferences = array();
		$this->setSavedPrefs();
		$allCats = $this->getAllCats();
		$this->categortyIDs = array();
		foreach ($allCats as $cat){
			$pref = $this->makePreference(new MyPrefsItem_PRLS($cat));
			$item_id = $pref->item->id;
			$this->categortyIDs[] = $item_id;
			$this->preferences[$item_id] = $pref;
		}
		$allItems = $this->getAllItems();
		foreach ($allItems as $item){
			$pref = $this->makePreference(new MyPrefsItem_PRLS($item));
			$item_id = $pref->item->id;
			$this->preferences[$item_id] = $pref;
			$parent_id = $pref->item->parent;
			if($this->getPref($parent_id))
				$this->getPref($parent_id)->item->addChild($item_id);
		}
		return $this->preferences;
	}
	
	public function prefExists($key){
		return array_key_exists($key, $this->preferences);
	}

	public function getPref($item_id){
		if($this->prefExists($item_id))
			return $this->preferences[$item_id];
		return null;
	}
	
	protected function makePreference($item){
		$pref = $this->getSavedPref($item->id);
		return new MyPrefsPreference_PRLS($this->user, $item, $pref['type'], $pref['id']);
	}
	
	protected function setSavedPrefs(){
		$sql = "SELECT * FROM ". $this->core->preferencesTable ." WHERE user_id=".$this->user;
		$results = $this->DB->get_results($sql);
		$this->savedPrefs = array();

		foreach ($results as $result){
			$this->savedPrefs[$result->item_id] = array(
							'id' => $result->id, 
							'type' => $this->prefTypes->get($result->preftype_id) );
		}
		return $this->savedPrefs;
	}
	/*
	protected function getItemPrefType($item_id){
		if(array_key_exists($item_id, $this->savedPrefs))
			return $this->savedPrefs[$item_id];
		
		return array('id' => null, 'type' => $this->prefTypes->getDefault());
		
	}*/

    public function getSavedPref($item_id, $default = true){
        if(array_key_exists($item_id, $this->savedPrefs))
            return $this->savedPrefs[$item_id];
        return $default ? array('id' => null, 'type' => $this->prefTypes->getDefault()) : null;
    }

    protected function getAllCats(){
		$sql = "SELECT * FROM ". $this->core->itemsTable ." WHERE category IS NULL ORDER BY name ASC";
		return $this->DB->get_results($sql, ARRAY_A);
	}

	protected function getAllItems(){
		$sql = "SELECT * FROM ". $this->core->itemsTable ." WHERE category IS NOT NULL ORDER BY name ASC";
		return $this->DB->get_results($sql, ARRAY_A);
	}

	public function savePref($itemId, $prefTypeId, $prefId = null){
		$current = $this->getSavedPref($itemId, false);
		$result = true;
        $prefType = $this->prefTypes->get($prefTypeId);
        if(is_null($prefType))
            return null;

		if(!$current && !$prefType->default){
			$result = $this->dbAddPref($itemId, $prefTypeId);
			$this->deleteChildrenPrefs($itemId);
		}
		elseif($current && !$prefType->default){
			if($current['type']->id != $prefTypeId){
				$result = $this->dbUpdatePref($current['id'], $prefTypeId);
				$this->deleteChildrenPrefs($itemId);
			}
		}
		elseif($current && $prefType->default){
			$result = $this->dbDeletePref($current['id']);
		}
		else{
			return null;
		}
		$this->load();

		return $result;
		
	}

	public function resetPrefs(){
		$deleted = $this->DB->delete( 
							$this->core->preferencesTable, 
							array( 'user_id' => $this->user ) );
		$this->load();
		return $deleted !== false ? true : false;
	}

	public function deleteChildrenPrefs($cat_id){
		if(in_array($cat_id, $this->categortyIDs)){
			$cat = $this->getPref($cat_id);
			if($cat){
				foreach($cat->item->children as $childId){
					if($child = $this->getSavedPref($childId, false)){
						$this->dbDeletePref($child['id']);
					}
				}
			}
		}
	}

	public function dbAddPref($item_id, $prefTypeId){
		$data = array(
				'user_id' => $this->user,
				'preftype_id' => $prefTypeId,
				'item_id'	=> $item_id
			);
		$result = $this->DB->insert( $this->core->preferencesTable, $data);
		return $result ? $this->DB->insert_id : false;
	}

	public function dbUpdatePref($prefId, $prefTypeId){
		$result = $this->DB->update(
						$this->core->preferencesTable, 
						array('preftype_id' => $prefTypeId), 
						array(
							//'user_id' => $user, 
							'id'	 => $prefId
							));
		return $result ? true : false;
	}

	public function dbDeletePref($prefId){
		$result = $this->DB->delete(
						$this->core->preferencesTable,  
						array(
							//'user_id' => $user, 
							'id'	 => $prefId
							));
		return $result ? true : false;
	}
	
	public function getAll(){
		return $this->getPreferences();
	}
	
	public function toArray(){
		$arr = array();
		foreach ($this->getAll() as $key => $pref){
			$arr[$key] = $pref->toArray();
		}
		return array(
				'preferences' => $arr,
				'categories' => $this->categortyIDs,
				'prefTypes'	  => $this->prefTypes->toArray()
		);
	}

	public function toJson(){
		return json_encode($this->toArray());
	}
	
	
}
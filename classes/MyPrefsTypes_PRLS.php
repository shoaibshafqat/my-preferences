<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsTypes_PRLS {
	protected $default;
	protected $values;
	protected $DB;
	protected $core;
	protected $tableName;
	
	public function __construct(){
		global $wpdb, $myPrefsCore;
		$this->DB = $wpdb;
		$this->core = $myPrefsCore;
		$this->tableName = $this->core->prefTypesTable;
		$this->load();
	}
	
	public function load(){
		$this->default = (object) array('id' => 0, 'name' => 'No Preference', 'default' => true);
		$this->values = $this->DB->get_results("SELECT * FROM ". $this->tableName);
        foreach($this->values as $key => $value){
            $this->values[$key]->id = (int) $value->id;
            if(!property_exists($value, 'default'))
                $this->values[$key]->default = false;
        }
		
	}
	
	
	public function getAll($withDefault = true){
		if($withDefault)
			return array_merge(array($this->default), $this->values);
		return $this->values;
	}
	
	public function toArray(){
		$arr = array();
		foreach ($this->getAll(true) as $key => $type){
			$arr[$key] = (array) $type;
		}
		return $arr;
	}
	
	public function get($id = null){
		foreach ($this->getAll(true) as $type){
			if($type->id == $id)
				return $type;
		}
		return null;
	}
	
	public function getDefault(){
		return $this->default;
	}

	public function isDefault($id){
		return $id == $this->getDefault()->id;
	}
	
}
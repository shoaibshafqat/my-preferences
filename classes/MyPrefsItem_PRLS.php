<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsItem_PRLS {
	
	public $id;
	public $name;
	public $parent = null;
	public $children = array();
	public $family;
	public $image;
	
	protected $_aliases = array(
						'category' => 'parent', 
						'type'=> 'family');

	public function __construct($properties = array()){
		if(!empty($properties))
			$this->fill($properties);
	}
	
	public function fill($properties){
		foreach ($properties as $property => $value){
			if(property_exists($this, $property)){
				$this->$property = $value;
			}
			elseif (array_key_exists($property, $this->_aliases)){
				$alias = $this->_aliases[$property];
				$this->$alias = $value;
			}
			
		}
	}
	
	public function addChild($id){
		if(!in_array($id, $this->children))
			$this->children[] = (int) $id;
	}
	
	public function toArray(){
		return array(
				'id' => (int) $this->id,
				'name' => $this->name,
				'parent' => $this->parent ? (int) $this->parent : $this->parent ,
				'children' => $this->children,
				'family' => $this->family,
				'image' => $this->image
		);
	}
	
	public function toJson(){
		return json_encode($this->toArray());
	}
}
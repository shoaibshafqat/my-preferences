<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsPreference_PRLS {
	
	public $id;
	public $user;
	public $item;
	public $type;
	
	public function __construct($user, $item, $type, $id = null){
		$this->user = (int) $user;
		$this->item = $item;
		$this->type = $type;
		$this->id = (int) $id;
	}
	
	public function toArray(){
		return array(
			'id' => $this->id,
			'user' => $this->user,
			'item' => $this->item->toArray(),
			'type' => (array) $this->type
		);
	}
	

	public function toJson(){
		return json_encode($this->toArray());
	}
}
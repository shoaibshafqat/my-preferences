<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsInstaller_PRLS{
	
	protected $core;
	protected $prefTypes = array('Never', 'Sometimes', 'Often');
	public function __construct(MyPreferences_PRLS $core){
		$this->core = $core;
		
	}
	
	public function install(){
		$this->createTables();
		$this->createMyPrefsPage();
	}
	
	protected function createTables(){
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		if($wpdb->get_var("SHOW TABLES LIKE '".$this->core->itemsTable."'") != $this->core->itemsTable) {
			$items = "CREATE TABLE ". $this->core->itemsTable ." (
				id int NOT NULL AUTO_INCREMENT,
				name varchar(50) NOT NULL,
				type varchar(50) NOT NULL,
				category int DEFAULT NULL,
				image varchar(150) NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			
			dbDelta( $items );

            $this->sampleData();
		}
		
		if($wpdb->get_var("SHOW TABLES LIKE '".$this->core->prefTypesTable."'") != $this->core->prefTypesTable) {
			$prefTypes = "CREATE TABLE ". $this->core->prefTypesTable ." (
				id int NOT NULL AUTO_INCREMENT,
				name varchar(50) NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			
			dbDelta($prefTypes);
			
			$this->insertPrefTypes();
		}
		
		if($wpdb->get_var("SHOW TABLES LIKE '".$this->core->preferencesTable."'") != $this->core->preferencesTable) {
			$myPrefs = "CREATE TABLE ". $this->core->preferencesTable ." (
				id int NOT NULL AUTO_INCREMENT,
				user_id int NOT NULL,
				preftype_id int NOT NULL,
				item_id int NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			
			dbDelta($myPrefs);
		}
	}
	
	protected function insertPrefTypes(){
		global $wpdb;
		foreach ($this->prefTypes as $pref){
			$wpdb->insert($this->core->prefTypesTable, array('name' => $pref));
		}
	}
	
	protected function createMyPrefsPage(){
		$page_id = $this->core->getPage();
		if(is_null($page_id) || is_null(get_post($page_id))){
			$page = array(
					'post_content'   => '['.$this->core->shortcode.']',
					'post_name'      => 'my-preferences',
					'post_title'     => 'My Preferences',
					'post_status'    => 'publish',
					'post_type'      => 'page'	
			);
			$page_id = wp_insert_post($page);
			if($page_id > 0)
				$this->core->setPage($page_id);
		}
	}


    protected function sampleData(){
        global $wpdb;
		$image_url = get_option('myprefs_images_url', $this->core->defaultImagesUrl);
        $sql ="INSERT INTO `oo_items` (`id`, `name`, `type`, `category`, `image`) VALUES
                (1, 'Berries', 'f', NULL, '".$image_url."/berries.jpg'),
                (2, 'Greens', 'v', NULL, '".$image_url."/greens.jpg'),
                (3, 'Grapes', 'f', NULL, '".$image_url."/grapes.jpg'),
                (4, 'Cranberries', 'f', 1, '".$image_url."/cranberries.jpg'),
                (5, 'Raspberries', 'f', 1, '".$image_url."/raspberries.jpg'),
                (6, 'Blueberries', 'f', 1, '".$image_url."/blueberries.jpg'),
                (7, 'Arugula', 'v', 2, '".$image_url."/arugula.jpg'),
                (8, 'Bok Choy', 'v', 2, '".$image_url."/bok-choy.jpg'),
                (9, 'Greens - Kale', 'v', 2, '".$image_url."/kale.jpg'),
                (10, 'Grapes - Green', 'f', 3, '".$image_url."/grapes-green.jpg'),
                (11, 'Grapes - Red', 'f', 3, '".$image_url."/grapes-red.jpg')";
        $wpdb->query($sql);


    }

}
<?php
/*
 Plugin Name: Woocommerce My Preferences
 Description: Enables a woocommerce customer to view a list of items and their category and allow him/her to create preferences for those categories and/or items in the list.
 Version: 1.0.2
 Author: LeadSoft
 Author URI: https://xpertzgroup.com
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPreferences_PRLS{
	
	public $baseDir;
	public $baseUrl;
	public $viewsDir;
	public $cssUrl;
	public $jsUrl;
	public $defaultImagesUrl;
	public $imagesUrl;

	public $messages;
	
	public $itemsTable = 'oo_items';
	public $prefTypesTable = 'oo_item_preftypes';
	public $preferencesTable = 'oo_preferences';
	
	public $shortcode = 'show_my_preferences';
	
	public $settings;

	protected $page;
	
	public function __construct(){
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}
		spl_autoload_register( array( $this, 'autoload' ) );
		
		$this->setDefaults();
		register_activation_hook( __FILE__, array( 'MyPreferences_PRLS', 'activate' ) );
		$this->initClasses();
		$this->addHooks();
	}


	
	public function autoload( $class )
	{
		if(strpos($class, 'MyPrefs') === 0 )
		{
			$path = $this->baseDir . 'classes/';
			$file = $class . '.php';
	
			if ( is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}
	}
	
	public function activate(){
		$installer = new MyPrefsInstaller_PRLS(new MyPreferences_PRLS());
		$installer->install();
	}
	
	protected function setDefaults(){
		$this->baseDir = plugin_dir_path(__FILE__);
		$this->baseUrl = plugin_dir_url(__FILE__);
		$this->viewsDir = $this->baseDir.'/views';
		$this->cssUrl = $this->baseUrl.'/assets/css';
		$this->jsUrl =  $this->baseUrl.'/assets/js';
		$this->defaultImagesUrl = $this->baseUrl.'images';
		$this->messages = include_once $this->baseDir . '/messages.php';
		
	}
	
	protected function addHooks(){
		
	}
	
	protected function initClasses(){
		$this->settings = new MyPrefsSettings_PRLS($this);
		$page = new MyPrefsPage_PRLS($this);
	}
	
	public function getPage(){
		return get_option('myprefs_page_id', null);
	}
	
	public function setPage($id){
		update_option('myprefs_page_id', $id);
	}

	public function isDebug(){
		return WP_DEBUG;
	}

}

$GLOBALS['myPrefsCore'] = new MyPreferences_PRLS();

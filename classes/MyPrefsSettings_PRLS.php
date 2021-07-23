<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsSettings_PRLS {
	
	protected $core;
	protected $tempImportFile;

	public function __construct($core){		
		global $wpdb;
		$this->core = $core;
		$this->tempImportFile = $core->baseDir . '/.tempcsv';
		$this->DB = $wpdb;
		add_action('admin_menu', array($this, 'addAdminMenus'));
	
	}

    public function addAdminMenus()
	{
		$settings_page = add_menu_page(
				'My Preferences',
				'My Preferences',
				'manage_options',
				'myprefs-settings',
				array( $this, 'makeSettingsPage' )
		);
	
		$import_page = add_submenu_page(
				'myprefs-settings',
				'My Preferences - Import Items',
				'Import Items',
				'manage_options',
				'myprefs-settings-import',
				array( $this, 'makeImportPage') );
		/*
		add_action('admin_print_scripts-' . $print_page_hook , array($this->roster_printer, 'enqueueScripts'));
		add_action('admin_print_scripts-' . $settings_page_hook , array($this->roster_settings, 'enqueueScripts')); */
	}

	public function getImagesUrl(){
		return get_option('myprefs_images_url', $this->core->defaultImagesUrl);
	}

	public function setImagesUrl($value){
		update_option('myprefs_images_url', $value);
	}

	public function setCustomCss($value){}

	public function makeSettingsPage() {
		$savedMsg = null;
		if(isset($_POST['myprefs_save_settings'])){
			$img_uri = esc_url_raw($_POST['images_url']);
			if(strpos($img_uri, 'http') === 0){
				$images_url = $img_uri;
			}
			elseif(strpos($img_uri, '/') === 0){
				$images_url = home_url(''). $img_uri;				
			}
			elseif(strpos($img_uri, '/') === false){
				$images_url = home_url('/'). $img_uri;				
			}
			else{
				$images_url = $this->getImagesUrl();
			}
			$images_url = substr($images_url, -1) == '/' 
						? substr($images_url, 0, -1)
						: $images_url;

			$this->setImagesUrl($images_url);

			$savedMsg = true;
		}
		$home_url = home_url('');
		$images_url = str_replace($home_url, '', $this->getImagesUrl());
		include_once $this->getView('settings');
	}

	public function makeImportPage() {
		$savedMsg = null;
		$clearData = isset($_POST['clear_data']) ? sanitize_text_field($_POST['clear_data']) : false;
		$imported = false;
		$log = "";
		if(isset($_POST['myprefs_import_cats'])){
			if($this->uploadCsv()){				
				$imported = $this->importCats($clearData);
				$savedMsg = !$imported || $imported['inserted'] == 0 ? false : true;
			}else{
				$savedMsg = false;
			}
		}

		if(isset($_POST['myprefs_import_items'])){
			if($this->uploadCsv()){
				$imported = $this->importItems($clearData);
				$savedMsg = !$imported || $imported['inserted'] == 0 ? false : true;
			}else{
				$savedMsg = false;
			}
		}
		if($imported){
			$log .='<p><span class="log_head">Found rows : </span>'. $imported['found'];
			$log .=', <span class="log_head">Rows with errors : </span>'. $imported['errors_count'];
			$log .=', <span class="log_head">Inserted rows : </span>'. $imported['inserted'];
			if(!empty($imported['errors'])){
				$log .= '<br/><p><strong>Details:-</strong><ul>';
				foreach ($imported['errors'] as $error) {
					$log.='<li>'. $error . '</li>';
				}
				$log .= '</ul></p></p>';

			}
		}
		$imgUrl = $this->core->baseUrl . '/assets/img';
		include_once $this->getView('import-items');
	}

	public function getView($view) {
		return $this->core->viewsDir.'/'. $view .'.php';
	}
	protected function importCats($clearData = false){
		if($clearData){
			$this->DB->query("DELETE FROM ". $this->core->itemsTable . "");
			$this->DB->query("DELETE FROM ". $this->core->preferencesTable . "");
		}

		$data = $this->readCsv();
		$found_rows = 0;
		$inserted_rows = 0;
		$errors_count = 0;
		$errors = array();
		if(is_array($data)){
			foreach ($data as $r) {
				$found_rows +=1;

				if(is_null($r['name']) || empty($r['name'])){
					$errors[] = 'Item name missing in row number '.$found_rows.'.';
					$errors_count++;
					continue;
				}

				$exists_query = "SELECT id FROM ". $this->core->itemsTable . 
								" WHERE name='".$r['name']."' LIMIT 1";

				
				$exists = is_null($this->DB->get_var($exists_query)) ? false : true;
				if($exists){
					$errors[] = 'An item name '.$r['name'].' alread exists.';
					$errors_count++;
					continue;
				}
				unset($r['category']);
				$inserted = $this->DB->insert($this->core->itemsTable, $r);
				if($inserted){
					$inserted_rows++;
				}
				else{
					$errors[] = 'Error inserting item '.$r['name'];
					$errors_count++;
				}
			}
		}
		else{
			return false;
		}

		return array(
				'found' => $found_rows,
				'inserted' => $inserted_rows,
				'errors_count' => $errors_count,
				'errors' => $errors
			);

	}

	protected function readCsv(){
		$importer = new MyPrefsCsvImporter_PRLS($this->tempImportFile, true);
		$this->deleteTempCsv();
		return $importer->get();
	}

	protected function getField($row, $index, $default = null){
		return isset($row[$index]) && !is_null($row[$index]) ? $row[$index] : $default;
	}

	protected function importItems($clearData = false){
		if($clearData){
			$this->DB->query("DELETE FROM ". $this->core->itemsTable . " WHERE category IS NOT NULL");
			$this->DB->query("DELETE FROM ". $this->core->preferencesTable . "");
		}

		$data = $this->readCsv();
		$found_rows = 0;
		$inserted_rows = 0;
		$errors_count = 0;
		$errors = array();
		if(is_array($data)){
			$sql = "SELECT * FROM ". $this->core->itemsTable ." WHERE category IS NULL";
			$cats_ = $this->DB->get_results($sql);
			$cats = array();
			foreach ($cats_ as $key => $cat) {
				$cats[$cat->name] = $cat->id;
			}
			foreach ($data as $r) {
				$found_rows +=1;

				if(is_null($r['name']) || empty($r['name'])){
					$errors[] = 'Item name missing in row number '.$found_rows.'.';
					$errors_count++;
					continue;
				}

				$exists_query = "SELECT id FROM ". $this->core->itemsTable . 
								" WHERE name='".$r['name']."' LIMIT 1";

				
				$exists = is_null($this->DB->get_var($exists_query)) ? false : true;
				if($exists){
					$errors[] = 'An item name '.$r['name'].' alread exists.';
					$errors_count++;
					continue;
				}
				if(!$r['category'] || !array_key_exists($r['category'], $cats)){
					$errors[] = 'Category for row number '.$found_rows.' ('.$r['category'].') not found.';
					$errors_count++;
					continue;
				}
				$r['category'] = $cats[$r['category']];
				$inserted = $this->DB->insert($this->core->itemsTable, $r);
				if($inserted){
					$inserted_rows++;
				}
				else{
					$errors[] = 'Error inserting item '.$r['name'];
					$errors_count++;
				}
			}
		}
		else{
			return false;
		}
		
		return array(
				'found' => $found_rows,
				'inserted' => $inserted_rows,
				'errors_count' => $errors_count,
				'errors' => $errors
			);

	}

	protected function deleteTempCsv(){
		if(file_exists($this->tempImportFile)){
			unset($this->tempImportFile);
			wp_delete_file($this->tempImportFile);
		}
	}

	protected function uploadCsv(){
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			  require_once( ABSPATH . 'wp-admin/includes/file.php' );
		  }
		  // for multiple file upload.
		  $upload_overrides = array( 'test_form' => false );
		  $files = $_FILES['csv'];
		  if ( $files['name'] ) {
			  $file = array(
				  'name' => $files['name'],
				  'type' => $files['type'],
				  'tmp_name' => $files['tmp_name'],
				  'error' => $files['error'],
				  'size' => $files['size']
			  );
	   
			$movefile = wp_handle_upload( $file, $upload_overrides );
			if(isset($movefile['file'])){
				
				$this->tempImportFile = $movefile['file'];
				return true;
				}
		  }
	  return false;
	}
}
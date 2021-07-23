<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsPage_PRLS {

    protected $core;
    protected $userPrefs;
    protected $filters = array(
        'default' => '0',
        'options' => array(
            '0' => 'All Items',
            'f' => 'Fruits',
            'v' => 'Vegetables'
        )
    );

    public function __construct(MyPreferences_PRLS $core) {
        $this->core = $core;
        $this->registerHooks();
    }

    public function getUser() {
        return get_current_user_id();
    }

    public function getUserPrefs() {
        if (!$this->getUser())
            return false;

        if (!$this->userPrefs) {
            $this->userPrefs = new MyPrefsUserPreferences_PRLS($this->getUser());
        }
        return $this->userPrefs;
    }

    protected function registerHooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_shortcode($this->core->shortcode, array($this, 'renderItems'));
        add_action('wp_ajax_myprefs_load_data', array($this, 'getPrefsData'));
		add_action('wp_ajax_nopriv_myprefs_load_data', array($this, 'getPrefsData'));
        add_action('wp_ajax_myprefs_update_pref', array($this, 'updatePref'));
        add_action('wp_ajax_myprefs_reset_prefs', array($this, 'resetPrefs'));
    }

    public function enqueueScripts() {
        wp_enqueue_style('myprerfs_page_css', $this->core->cssUrl . '/page.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-accordion');

        wp_enqueue_script(
                'myprerfs_page_js', $this->core->jsUrl . '/page.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-accordion'), '1.0.0', true);
        $this->setJsInitParams('myprerfs_page_js');
    }

    protected function setJsInitParams($script) {
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$getUserPrefs = $this->getDirectPrefsData();
            
        $params = array(
            'ajaxUrl' => admin_url('admin-ajax.php', $protocol),
            'imagesUrl' => $this->core->settings->getImagesUrl(),
            'isDebug' => $this->core->isDebug(),
            'messages' => $this->core->messages,
			'getUserPrefs' => $getUserPrefs,
        );
	
        wp_localize_script($script, 'myprefsInitVars', $params);
    }

    public function getPrefsData() {
        if (!$this->getUser()) {
            $this->renderJson(false, null, 'Not Allowed');
        }
        $this->renderJson(true, $this->getUserPrefs()->toArray());
    }
	
	public function getDirectPrefsData() {
        if (!$this->getUser() || $this->getUser() == 0) {
            return $this->renderJson(false, null, 'Not Allowed', 'json');
        }
        return $this->renderJson(true, $this->getUserPrefs()->toArray(), null, 'json');
    }

    public function updatePref() {

        if (!$this->getUser()) {
            $this->renderJson(false, null, 'Not Allowed');
        }

        $itemId = sanitize_text_field($_POST['itemId']);
        $saved = $this->getUserPrefs()->savePref(sanitize_text_field($_POST['itemId']), sanitize_text_field($_POST['prefType']), sanitize_text_field($_POST['prefId']));
        if (is_null($saved)) {
            $this->renderJson(false);
        } elseif (!$saved) {
            $this->renderJson(false);
        } else {
            $array = $this->getUserPrefs()->toArray();
            if (isset($array) && is_array($array)) {
                $prefs = $array['preferences'];
                $this->renderJson(true, array('preferences' => $prefs));
            } else {
                $this->renderJson(false);
            }
        }
    }

    public function resetPrefs() {
        if (!$this->getUser()) {
            $this->renderJson(false, null, 'Not Allowed');
        }

        if ($this->getUserPrefs()->resetPrefs()) {
            $array = $this->getUserPrefs()->toArray();
            if (isset($array) && is_array($array)) {
                $prefs = $array['preferences'];
                $this->renderJson(true, array('preferences' => $prefs));
            } else {
                $this->renderJson(false);
            }
        } else {
            $this->renderJson(false);
        }
    }

    protected function renderJson($success, $data = null, $msg = null, $print = null) {
        $jsonArr = array(
            'status' => $success ? 'OK' : 'ERROR',
            'message' => $msg,
            'data' => $data);
			if($print == 'json'){
				return json_encode($jsonArr);
				}else{
					echo json_encode($jsonArr);
					die();
					}
        
    }

    public function renderItems($atts) {
        //$this->getUserPrefs();
        if (!$this->getUser()) {
            include $this->core->viewsDir . '/not-allowed.php';
            return;
        }
        $filters = $this->filters['options'];
        $filter_default = $this->filters['default'];
        ob_start();
        include $this->core->viewsDir . '/user-preferences.php';
        return ob_get_clean();
    }

}

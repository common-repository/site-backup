<?php
/**
 * Plugin Name: Site Backup
 * Plugin URI: http://www.giribaz.com/
 * Description: Backup and restore your site in one click. Schedule automatic backup of your site. No worries anymore!!!
 * Version: 1.0.0
 * Author: Aftabul Islam
 * Author URI: http://www.giribaz.com
 * Requires at least: 4.4
 * Tested up to: 4.7
 */

// Security Check
if(!defined('ABSPATH')) die();

// OS independent directory seperator shortning
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Signature Macro of the plugin
define('GB_SITE_BACKUP', true);

/**
 *
 * @class GBSiteBackup
 * @description Main class of the plugin
 * 
 * */
class GBSiteBackup{

	/**
	 *
	 * @var string $name
	 * @description Name of the plugin
	 * 
	 * */
	public $name;
	
	/**
	 *
	 * @var string $prefix
	 * @description Unique Identifier of the plugin
	 *
	 * */
	public $prefix;

	/**
	 *
	 * @var string $version
	 * @description Version number of the plugin
	 *
	 * */
	public $version;

	/**
	 *
	 * @var string $path
	 * @description Root path of this plugin
	 * 
	 * */
	public $path;

	/**
	 *
	 * @var string $url
	 * @description Root URL of the plugin
	 *
	 * */
	public $url;

	/**
	 *
	 * @var string $upload_path
	 * @description Upload directory path of the plugin
	 *
	 * */
	public $upload_path; // Use this if you need upload directory

	/**
	 *
	 * @var string $upload_url
	 * @description Upload directory URL of the plugin
	 *
	 * */
	public $upload_url; // Use this if you need upload directory

	/**
	 *
	 * @var mixed $options
	 * @description Options store for the plugin
	 *
	 * */
	public $options;
	
	/**
	 * 
	 * @var array $shortcode_data
	 * @description Shortcode Data to be stored.
	 * 
	 * */
	public $shortcode_data;
	
	/**
	 * 
	 * @var string $shortcode_html
	 * @description Shortcode return html data storage.
	 * 
	 * */
	public $shortcode_html;
	
	/**
	 *
	 * @var string $website
	 * @description Website of the plugin
	 *
	 * */
	public $website;

	/**
	 *
	 * @var string $support
	 * @description Support page URL
	 *
	 * */
	public $support;

	/**
	 *
	 * @var string $feedback
	 * @description Feedback/Review Page URL
	 * 
	 * */
	public $feedback;

	/**
	 *
	 * @var string $logo_url
	 * @description URL of the logo
	 *
	 * */
	public $logo_url;
	
	/**
	 * 
	 * @var $file_info
	 * @description Stores all the file information
	 * 
	 * */
	public $file_info;
	
	/**
	 *
	 * @function __construct
	 * @description Main constructor function of the plugin
	 * @param string $name Name of the plugin
	 * @return void
	 * 
	 * */
	public function __construct($name){

		// Plugin data initialization
		$this->name = trim($name);
		$this->prefix = 'gb_' . str_replace(' ', '-', strtolower($this->name));
		$this->path = plugin_dir_path(__FILE__);
		$this->url = plugin_dir_url(__FILE__);

		// URLS and extras
		$this->version = '1.0.0';
		$this->website = 'www.giribaz.com';
		$this->support = '';
		$this->feedback = '';
		$this->logo_url = '';

		// Options
		$this->options = get_option($this->prefix);
		if(empty($this->options)) $this->options = array(
		    'gb-backup-database' => 'on',
		    'gb-backup-files' => 'on',
		    'gb-backup-frequency' => 'weekly',
		    'gb-number-of-backups' => 5,
		    'gb-exclude-folders' => '',
		    'gb-email-notification' => '',
			
		);
		register_shutdown_function(array(&$this, 'save_options')); // Works as destructor for the object

		// Working with upload directory
		$upload = wp_upload_dir(); // Upload Directory
		$this->upload_path = $upload['basedir'] . DS . $this->prefix; // Path
		$this->upload_url = $upload['baseurl'] . '/' . $this->prefix; // URL
		if( !is_dir($this->upload_path ) ) mkdir( $this->upload_path , 0755 ); // Creates the upload directory if it is not their

		// Admin assets Registration
		add_action('admin_enqueue_scripts', array(&$this, 'admin_assets') );
				
		// Adding menu
		add_action('admin_menu', array(&$this, 'menu'));
		
		// Ajax Call
		add_action('wp_ajax_full_backup', array(&$this, 'full_backup')); // Logged in users
		add_action('wp_ajax_delete_file', array(&$this, 'delete_file')); // Logged in users
		add_action('wp_ajax_restore', array(&$this, 'restore')); // Logged in users
		
		// Cron
		add_filter( 'cron_schedules', array( &$this, 'add_new_intervals' ) ); // New cron schedules
		add_action('gb_site_backup_action_hook', array( &$this, 'auto_backup' ) ); // Site backup hook
	}
		
	/**
	 * 
	 * @function admin_assets
	 * @description Loads admin assets
	 * @param void
	 * @return void
	 * 
	 * */
	public function admin_assets(){
		
		// Style Sheets
		wp_register_style( 'wpb-bootstrap-main-style', $this->url . 'external/bootstrap-3.3.7/css/bootstrap.min.css',  array(), '3.3.7'); // Bootstrap Main File.
		wp_register_style( 'wpb-bootstrap-material-theme-style', $this->url . 'external/bootstrap-3.3.7/css/bootstrap-theme.min.css', array('wpb-bootstrap-main-style'), '4.0.2' ); // Bootstrap Material Theme.
		wp_register_style( 'wpb-bootstrap-material-theme-ripples-style', $this->url . 'external/bootstrap-3.3.7/css/ripples.min.css', array('wpb-bootstrap-material-theme-style'), '4.0.2' ); // Bootstrap Material Theme.
		wp_register_style( 'wpb-admin-style', $this->url . 'assets/css/admin-style.css', array('wpb-bootstrap-material-theme-ripples-style') ); // Custom style for the frontend.
		
		// Javascript
		wp_register_script( 'wpb-bootstrap-main-script', $this->url . 'external/bootstrap-3.3.7/js/bootstrap.min.js', array('jquery'), '3.3.7' ); // Custom script for the frontend.
		wp_register_script( 'wpb-admin-script', $this->url . 'assets/js/admin-script.js', array('wpb-bootstrap-main-script') ); // Custom script for the frontend.
		
		wp_localize_script( 'wpb-admin-script', 'GB_AJAXURL', array( admin_url( 'admin-ajax.php' ) ) ); // Assigning GB_AJAXURL on the frontend
		wp_localize_script( 'wpb-admin-script', '_GB_SECURITY', array( wp_create_nonce( "gb-ajax-nonce" ) ) ); // Assigning GB_AJAXURL on the frontend
	}
	
	/**
	 *
	 * @function save_options
	 * @description Saves the option when the function closes
	 * @param void
	 * @return void
	 *
	 * */
	public function save_options(){
		update_option($this->prefix, $this->options);
	}
	
	/**
	 * 
	 * @function menu
	 * @description Adding admin menu to the WordPress admin dashboard
	 * 
	 * */
	public function menu(){
		
		// Adding main menu
		add_menu_page( 
			'Site Backup', // Title of the page
			'Site Backup', // Menu Title
			'administrator', // Capability
			'gb-site-backup' // Menu Slug
			//auto::  create_function('', 'require_once( plugin_dir_path( __FILE__ ) . "views" . DS . "admin" . DS . "menu.php" );'), // Function 
			//auto::  '', // Image URL
			//auto::  7 // Menu Position
		);
		
		// Adding submenu for main menu
		add_submenu_page( 
			'gb-site-backup', // Parent Slug
			'Site Backup', // Page title
			'Backup', // Submenu title
			'administrator', // User capabilities
			'gb-site-backup', // Menu Slug
			create_function('', 'require_once( plugin_dir_path( __FILE__ ) . "views" . DS . "admin" . DS . "backup.php" );')
		);
		
		// Adding submenu
		add_submenu_page( 
			'gb-site-backup', // Parent Slug
			'Site Backup Settings', // Page title
			'Settings', // Submenu title
			'administrator', // User capabilities
			'gb-site-backup-settings', // Menu Slug
			create_function('', 'require_once( plugin_dir_path( __FILE__ ) . "views" . DS . "admin" . DS . "settings.php" );')
		);
	}

	/**
	 * 
	 * @function full_backup
	 * @description Backing up full website
	 * @param void
	 * @return void
	 * 
	 * */
	public function full_backup(){
		
		if( ! wp_verify_nonce( $_POST['_gb_security'] ,'gb-ajax-nonce') || !current_user_can( 'manage_options' ) ) wp_die();
		check_ajax_referer('gb-ajax-nonce', '_gb_security');
		
		$file_name = $this->upload_path . DS . 'gb_manual_'.time().'_site_backup';
		
		// Creating Archive
		if( isset($this->options['gb-backup-files']) && $this->options['gb-backup-files'] == 'on'){
			
			// Exclude Directory
			$this->file_info['exclude']['directory'] = explode( ',', $this->options['gb-exclude-folders'] );
			$this->file_info['exclude']['directory'][] = $this->upload_path;
			
			$this->recursive_scan(ABSPATH);
			$this->archive_file($this->file_info['files'], $file_name . '.zip');
		}
		
		// Database Dumping
		if( isset($this->options['gb-backup-database']) && $this->options['gb-backup-database'] == 'on'){
			exec('mysqldump --user='. DB_USER .' --password='. DB_PASSWORD .' --host='. DB_HOST .' '. DB_NAME .' > '. $file_name . '.sql');
		}
		
		echo "Backed up successfully";
		
		wp_die();
	}
	
	/**
	 * 
	 * @function delete_file
	 * @description Delets archive files and folders
	 * @param void
	 * @return void
	 * 
	 * */
	public function delete_file(){
		
		if( ! wp_verify_nonce( $_POST['_gb_security'] ,'gb-ajax-nonce') || !current_user_can( 'manage_options' ) ) wp_die();
		check_ajax_referer('gb-ajax-nonce', '_gb_security');
		echo unlink( $this->upload_path . DS .  sanitize_file_name( $_POST['file'] ) ) && unlink( $this->upload_path . DS .  rtrim( sanitize_file_name( $_POST['file'] ) , 'zip') . 'sql' );
		wp_die();
	}

	/**
	 * 
	 * @function restore
	 * @description Restores archive file
	 * @param void
	 * @return void
	 * 
	 * */
	public function restore(){
		
		if( ! wp_verify_nonce( $_POST['_gb_security'] ,'gb-ajax-nonce') || !current_user_can( 'manage_options' ) ) wp_die();
		check_ajax_referer('gb-ajax-nonce', '_gb_security');
		
		$file_name = $this->upload_path . DS . sanitize_file_name($_POST['file']);
		
		if(file_exists($file_name)){
			$zip = new ZipArchive();
			$zip->open($file_name, true);
			$zip->extractTo(DS);
		} else echo 'File doesn\'t exits';
		
		if(file_exists(rtrim($file_name, 'zip') . '.sql')) exec('mysqldump --user='. DB_USER .' --password='. DB_PASSWORD .' --host='. DB_HOST .' '. DB_NAME .' < '. rtrim($file_name, 'zip') . '.sql');
		echo true;
		wp_die();
	}
	
	/**
	 * 
	 * @function recursive_scan
	 * @descriptions Scans the entire directory
	 * @param $path :: Path of the root folder
	 * @return void
	 * 
	 * */
	protected function recursive_scan($path){
		$path = rtrim($path, '/');
		if(!is_dir($path)) {
			
			$this->file_info['total-size'] += filesize($path);
			//auto::  pr($this->file_info['total-size']);
			$large_file_size = 3 * 1024 * 1024 ; // Large files are >= 3 megabytes
			
			/*if( filesize($path) >= $large_file_size ){
			
				$this->file_info['large-files'][] = array('path' => $path, 'size'=> $this->format_size(filesize($path)));
			
			} else*/ $this->file_info['files'][] = array('path' => $path, 'size'=> $this->format_size(filesize($path)));
			
			} else {
			
				$files = scandir($path);
				foreach($files as $file) if( $file != '.' && $file != '..' && !in_array($file, $this->file_info['exclude']['directory']) ) $this->recursive_scan($path . '/' . $file);
			
			}
	}
	
	/**
	 * 
	 * @function archive_file
	 * @description Archives single file or an array of files
	 * @param $file :: Path of the file
	 * @param $archive_path :: Path of the archive file
	 * @return bool
	 * 
	 * */
	public function archive_file($file , $archive_path ){
		
		if( !is_array($file) ){ // Archiving single files
			
			$zip = new ZipArchive();
			$zip->open($archive_path, true);
			return $zip->addFile($file);
			
		} else { // Archiving multiple files
			
			$zip = new ZipArchive();
			$zip->open($archive_path, true);
			
			foreach($file as $f) $zip->addFile($f['path']);
			
			}
	}
	
	/**
	 * 
	 * @function format_size
	 * @description Formats the size from bytes to others
	 * @param $file_size :: Size of the file
	 * @return string :: Formatted size
	 * 
	 * */
	protected function format_size($file_size){
		$units = array(
			'TB(tera byte)' => 1099511627776,
			'GB(giga byte)' => 1073741824,
			'MB(mega byte)' => 1048576,
			'KB(kilo byte)' => 1024,
			'B(byte)' => 1,
		);
		
		foreach($units as $key => $value){
			if($file_size < $value) continue;
			return ceil($file_size / $value ) . ' ' . $key;
		}
	}
	
	/**
	 * 
	 * @function add_new_intervals
	 * @description Adds new intervals to the cron
	 * @param array $schedules :: Schedules of all the crons
	 * @return array $schedules :: New schedules of the cron
	 * 
	 * */
	public function add_new_intervals($schedule){
		
		$schedule['gbsb_weekly'] = array(
			'interval' => 7 * 24 * 60 * 60,
			'display' => __('Weekly'),
		);

		$schedule['gbsb_bi_weekly'] = array(
			'interval' => 2 * 7 * 24 * 60 * 60,
			'display' => __('Bi Weekly'),
		);

		$schedule['gbsb_monthly'] = array(
			'interval' => 30 * 24 * 60 * 60,
			'display' => __('Monthly'),
		);

		$schedule['gbsb_test'] = array(
			'interval' => 15 * 60,
			'display' => __('Test'),
		);
		
		return $schedule;
	}
	
	/**
	 * 
	 * @function auto_backup
	 * @description Auto backup functionality
	 * @param void
	 * @return void
	 * 
	 * */
	public function auto_backup(){
		
		$file_name = $this->upload_path . DS . 'gb_auto_'.time().'_site_backup';
		
		// Creating Archive
		if( isset($this->options['gb-backup-files']) && $this->options['gb-backup-files'] == 'on'){
			
			// Exclude Directory
			$this->file_info['exclude']['directory'] = explode( ',', $this->options['gb-exclude-folders'] );
			$this->file_info['exclude']['directory'][] = $this->upload_path;
			
			$this->recursive_scan(ABSPATH);
			$this->archive_file($this->file_info['files'], $file_name . '.zip');
		}
		
		// Database Dumping
		if( isset($this->options['gb-backup-database']) && $this->options['gb-backup-database'] == 'on'){
			exec('mysqldump --user='. DB_USER .' --password='. DB_PASSWORD .' --host='. DB_HOST .' '. DB_NAME .' > '. $file_name . '.sql');
		}
		
	}

}

/**
 * 
 * @function pr
 * @description Formatted output of print_r function
 * @param mixed $obj
 * @return void
 * 
 * */
if(!function_exists('pr')):
function pr($obj){
	echo "<pre>"; 
	print_r($obj);
	echo "</pre>";
}
endif;

// Declaring the global variable for this plugin
global $gb_site_backup;
$gb_site_backup = new GBSiteBackup('Site Backup');

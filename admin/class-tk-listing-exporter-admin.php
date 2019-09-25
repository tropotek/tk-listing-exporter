<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.tropotek.com/
 * @since      1.0.0
 *
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/admin
 * @author     Mick Mifsud <info@tropotek.com>
 */
class Tk_Listing_Exporter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 * Add a settings page for this plugin to the Settings menu.
	 *
	 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	 *        Administration Menus: http://codex.wordpress.org/Administration_Mens
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_options_page( 'Listing Export Settings', 'Listing Export',
			'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));

		add_options_page('Client Update', '',
			'manage_options', 'tk-listing-export-client-edit', array($this, 'display_client_update_page'));
	}

	/**
	 * Add settings action link to the plugins page.
	 * Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' .
			__('Settings', $this->plugin_name) . '</a>',
		);
		return array_merge(  $settings_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
		//include_once(dirname(__FILE__) . '/../includes/class-ExportClient-list-table.php');
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-ExportClient-list-table.php';
		include_once('partials/tk-listing-exporter-admin-display.php');
	}

	/**
	 * Render the settings page for this plugin.
	 * @since    1.0.0
	 */
	public function display_client_update_page() {
		//include_once(dirname(__FILE__) . '/../includes/class-ExportClient-list-table.php');
		//require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-ExportClient-list-table.php';
		include_once('partials/tk-listing-exporter-client-edit.php');
	}

	/**
	 * admin/class-wp-cbf-admin.php\
	 */
	public function options_update() {
		//register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}

	/**
	 * admin/class-wp-cbf-admin.php
	 */
	public function validate($input) {
		// All checkboxes inputs
		$valid = array();

		return $valid;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in Tk_Listing_Exporter_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The Tk_Listing_Exporter_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tk-listing-exporter-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in Tk_Listing_Exporter_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The Tk_Listing_Exporter_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tk-listing-exporter-admin.js', array( 'jquery' ), $this->version, false );
	}

}

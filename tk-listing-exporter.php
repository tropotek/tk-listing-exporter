<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.tropotek.com/
 * @since             1.0.0
 * @package           Tk_Listing_Exporter
 *
 * @wordpress-plugin
 * Plugin Name:       Tk Listing Exporter
 * Plugin URI:        https://github.com/tropotek/tk-listing-exporter
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Mick Mifsud
 * Author URI:        http://www.tropotek.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tk-listing-exporter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TK_LISTING_EXPORTER_VERSION', '1.0.0' );
define( 'TK_LISTING_EXPORTER_URL_KEY', 'R2YFZ+#%E26v3-@mUj1_@P2Edj_3hkaa8j+k*Wd|K4wDZ|27L4cXoLlg]WoSONuW');
define( 'TK_LISTING_EXPORTER_NAME', 'tk-listing-exporter' );

if (!session_id() && !headers_sent())
	session_start();

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tk-listing-exporter-activator.php
 */
function activate_tk_listing_exporter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tk-listing-exporter-activator.php';
	Tk_Listing_Exporter_Activator::activate(TK_LISTING_EXPORTER_NAME);
}

function update_tk_listing_exporter() {
	if ( get_site_option( 'tk_listing_exporter_db_version' ) != TK_LISTING_EXPORTER_VERSION ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-tk-listing-exporter-activator.php';
		Tk_Listing_Exporter_Activator::update(TK_LISTING_EXPORTER_NAME);
	}
}
add_action( 'plugins_loaded', 'update_tk_listing_exporter' );



/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tk-listing-exporter-deactivator.php
 */
function deactivate_tk_listing_exporter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tk-listing-exporter-deactivator.php';
	Tk_Listing_Exporter_Deactivator::deactivate(TK_LISTING_EXPORTER_NAME);
}

register_activation_hook( __FILE__, 'activate_tk_listing_exporter' );
register_deactivation_hook( __FILE__, 'deactivate_tk_listing_exporter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tk-listing-exporter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tk_listing_exporter() {

	$plugin = new Tk_Listing_Exporter();
	$plugin->run();

}
run_tk_listing_exporter();

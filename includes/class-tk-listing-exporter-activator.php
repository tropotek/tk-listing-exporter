<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.tropotek.com/
 * @since      1.0.0
 *
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/includes
 * @author     Mick Mifsud <info@tropotek.com>
 */
class Tk_Listing_Exporter_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate($plugin_name) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'tk_listing_export_client';

		// If the author value is 0 then all listings are exported
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(128) NOT NULL,
			secret varchar(32) NOT NULL,
			author_id INT UNSIGNED NOT NULL DEFAULT 0,
			last_cache DATETIME NULL,
			active varchar(8) NOT NULL DEFAULT '1',
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( 'tk_listing_exporter_db_version', TK_LISTING_EXPORTER_VERSION );

	}


	public static function update($plugin_name) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'tk_listing_export_client';

		// TODO: On upgrade of plugin
		/*
		$installed_ver = get_option( "jal_db_version" );
		if ( $installed_ver != TK_LISTING_EXPORTER_VERSION ) {

			$table_name = $wpdb->prefix . 'liveshoutbox';
			$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name tinytext NOT NULL,
		text text NOT NULL,
		url varchar(100) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
	);";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( "jal_db_version", TK_LISTING_EXPORTER_VERSION );
		}
		*/
	}

}

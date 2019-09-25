<?php


/**
 * Insert a new ExportClient
 *
 * @param array|int\bool $args
 */
function tk_save_ExportClient( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'        => null,
        'name'      => '',
        'secret'    => '',
        'author_id' => 0,
        'last_cache' => null,
        'active' => ''
    );

    $args       = wp_parse_args( $args, $defaults );
    $table_name = $wpdb->prefix . 'tk_listing_export_client';

    // some basic validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No Name provided.', TK_LISTING_EXPORTER_NAME ) );
    }
    if ( empty( $args['secret'] ) ) {
        return new WP_Error( 'no-secret', __( 'No Secret Key provided.', TK_LISTING_EXPORTER_NAME ) );
    } else if (strlen($args['secret']) > 32) {
	    return new WP_Error( 'no-secret', __( 'The Secret Key cannot be longer than 32 characters.', TK_LISTING_EXPORTER_NAME ) );
    }
	if ( !isset($args['author_id']) ||  $args['author_id'] === '' || $args['author_id'] === null) {
		return new WP_Error( 'no-author_id', __( 'No Listing Author provided.', TK_LISTING_EXPORTER_NAME ) );
	}

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset($args['id']);

    if (!$row_id) {      // insert a new
        if ($wpdb->insert($table_name, $args)) {
	        // Inserted
        }
        return $wpdb->insert_id;
    } else {                // do update method here
        if ($test = $wpdb->update( $table_name, $args, array( 'id' => (int)$row_id ))) {
        	// Updated
        }
	    return $row_id;
    }
//    error_log($wpdb->print_error());
    return false;
}

/**
 * Get all Export Client
 *
 * @param $args array
 *
 * @return array
 */
function tk_get_all_ExportClient( $args = array(), $cache = true ) {
    global $wpdb;

    $defaults = array(
	    'number'     => 25,
	    'offset'     => 0,
	    'orderby'    => 'id',
	    'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'Export Client-all';
    $items = false;

    if ($cache)
        $items = wp_cache_get( $cache_key, '' );
    if ( false === $items ) {
	    $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'tk_listing_export_client ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );
	    wp_cache_set( $cache_key, $items, '' );
    }

    return $items;
}

/**
 * Fetch all Export Client from database
 *
 * @return array|int
 */
function tk_get_ExportClient_count() {
    global $wpdb;
    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'tk_listing_export_client' );
}

/**
 * Fetch a single Export Client from database
 *
 * @param int   $id
 *
 * @return array|stdClass
 */
function tk_get_ExportClient( $id = 0 ) {
    global $wpdb;
    if ($id)
        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'tk_listing_export_client WHERE id = %d', $id ) );

    $obj = new stdClass();
    $obj->id = 0;
    $obj->name = '';
    $obj->secret = '';
    $obj->author_id = 0;
    $obj->active = '';
    $obj->last_cache = '';
    return $obj;
}

/**
 * Fetch a single Export Client from database
 *
 * @param int   $id
 *
 * @return array|stdClass
 */
function tk_get_by_secret_ExportClient( $secret ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'tk_listing_export_client WHERE secret = \'%s\' LIMIT 1', $secret ) );
}

/**
 * Fetch a single Export Client from database
 *
 * @param int   $id
 *
 * @return int
 */
function tk_delete_ExportClient( $id = 0 ) {
    global $wpdb;
	if ($id)
        return $wpdb->query( sprintf('DELETE FROM ' . $wpdb->prefix . 'tk_listing_export_client WHERE id = %d', $id) );
	return 0;
}
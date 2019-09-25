<?php

if ( ! class_exists ( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class Tk_Listing_Export_Client extends \WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'ExportClient',
			'plural'   => 'ExportClients',
			'ajax'     => false
		) );

		global $wp_object_cache;
        return $wp_object_cache->flush();
	}

	function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	function no_items() {
		_e( 'No Clients Defined', '' );
	}

	/**
	 * Default column values if no callback found
	 *
	 * @param  object  $item
	 * @param  string  $column_name
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'id':
				return $item->id;

			case 'name':
				return $item->name;

			case 'secret':
				return $item->secret;

			case 'author_id':
				return $item->author_id;

            case 'last_cache':
                return $item->last_cache;

            case 'active':
                return $item->active;

			default:
				return isset( $item->$column_name ) ? $item->$column_name : '';
		}
	}

	/**
	 * Get the column names
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'id'      => __( 'ID', '' ),
			'name'      => __( 'Name', '' ),
			'secret'      => __( 'Secret Key', '' ),
			'author_id'      => __( 'Listing Author', '' ),
            'last_cache'      => __( 'Last Import', '' ),
            'active'      => __( 'Active', '' ),

		);

		return $columns;
	}

	/**
	 * Render the designation name column
	 *
	 * @param  object  $item
	 * @return string
	 */
//	function column_id( $item ) {
//
//		$actions           = array();
//		$actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=tk-listing-export-client-edit&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', '' ), __( 'Edit', '' ) );
//		$actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=tk-listing-export-client-manager&action=delete&id=' . $item->id ), $item->id, __( 'Delete this item', '' ), __( 'Delete', '' ) );
//
//		return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=tk-listing-export-client-edit&action=view&id=' . $item->id ), $item->id, $this->row_actions( $actions ) );
//	}

	/**
	 * Render the designation name column
	 *
	 * @param  object  $item
	 * @return string
	 */
	function column_name( $item ) {

		$actions           = array();
		$actions['edit']   = sprintf( '<a href="%s" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=tk-listing-export-client-edit&action=edit&id=' . $item->id ), $item->id, __( 'Edit this item', '' ), __( 'Edit', '' ) );
		$actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s" onclick="return confirm(\'Do you wish to delete this record?\');">%s</a>', admin_url( 'options-general.php?page=tk-listing-exporter&action2=trash&id=' . $item->id ), $item->id, __( 'Delete this item', '' ), __( 'Delete', '' ) );

		return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=tk-listing-export-client-edit&action=edit&id=' . $item->id ), $item->name, $this->row_actions( $actions ) );
	}
	/**
	 * Render the designation name column
	 *
	 * @param  object  $item
	 * @return string
	 */
	function column_author_id( $item ) {
		$val = 'All';
		if ($item->author_id) {
			// TODO: find the user and display the name
			$user = get_user_by('ID', $item->author_id);
			if ($user) {
				$val = $user->data->display_name;
			}
		}
		return sprintf('%s', $val);
	}
	function column_active($item)
    {
        if ($item->active)
            return 'Yes';
        return 'No';
    }

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', true ),
		);
		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	function get_bulk_actions() {
		$actions = array(
			'trash'  => __( 'Move to Trash', '' ),
		);
		return $actions;
	}

	/**
	 * Render the checkbox column
	 *
	 * @param  object  $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ExportClient_id[]" value="%d" />', $item->id
		);
	}

	/**
	 * Set the views
	 *
	 * @return array
	 */
	public function get_views_() {
		$status_links   = array();
		$base_link      = admin_url( 'admin.php?page=sample-page' );

		foreach ($this->counts as $key => $value) {
			$class = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
			$status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
		}

		return $status_links;
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	function prepare_items() {

		if ((!empty($_REQUEST['action2']) && $_REQUEST['action2'] == 'trash')) {
			if (!empty($_REQUEST['id'])) {
				tk_delete_ExportClient($_REQUEST['id']);
				wp_safe_redirect( remove_query_arg(array('action2', 'id')) );
				exit();
			}
			if (!empty($_REQUEST['ExportClient_id']) && is_array($_REQUEST['ExportClient_id'])) {
				foreach ($_REQUEST['ExportClient_id'] as $id) {
					tk_delete_ExportClient( $id );
				}
				wp_safe_redirect( remove_query_arg(array('action2', 'ExportClient_id')) );
				exit();
			}
		}

		$columns               = $this->get_columns();
		$hidden                = array( );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page              = 25;
		$current_page          = $this->get_pagenum();
		$offset                = ( $current_page -1 ) * $per_page;
		$this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

		// only necessary because we have sample data
		$args = array(
			'offset' => $offset,
			'number' => $per_page,
		);

		if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
			$args['order']   = $_REQUEST['order'] ;
		}

		$this->items  = tk_get_all_ExportClient( $args, false);

        $this->set_pagination_args( array(
	        'total_items' => tk_get_ExportClient_count(),
            'per_page'    => $per_page
        ) );

    }
}

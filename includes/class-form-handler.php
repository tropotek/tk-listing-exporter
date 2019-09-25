<?php

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Form_Handler {

	/**
	 * Hook 'em all
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_form' ) );

	}

	/**
	 * Handle the ExportClient new and edit form
	 *
	 * @return void
	 */
	public function handle_form() {
		if (!empty($_SESSION['tk-exporter']['form-errors'])) {
			foreach ($_SESSION['tk-exporter']['form-errors'] as $k => $v) {
				add_settings_error($k,'', $v,'error');
			}
			$_SESSION['tk-exporter']['form-errors'] = null;
			unset($_SESSION['tk-exporter']['form-errors']);
		}
		if (!empty($_SESSION['tk-exporter']['form-success'])) {
			add_settings_error('formSuccess','', __( 'Form Submitted Successfully', TK_LISTING_EXPORTER_NAME ),'updated');
			$_SESSION['tk-exporter']['form-success'] = null;
			unset($_SESSION['tk-exporter']['form-success']);
		}

		if ( ! isset( $_POST['submit-export-client'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'export-client' ) ) {
			die( __( 'Are you cheating?', TK_LISTING_EXPORTER_NAME ) );
		}
		if ( ! current_user_can( 'read' ) ) {
			wp_die( __( 'Permission Denied!', TK_LISTING_EXPORTER_NAME ) );
		}

		$errors   = array();
		$page_url = admin_url( 'admin.php?page=tk-listing-export-client-edit&action='.$_GET['action'] );

		$field_id = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : null;
		$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$secret = isset( $_POST['secret'] ) ? sanitize_text_field( $_POST['secret'] ) : '';
		$author_id = isset( $_POST['author_id'] ) ? sanitize_text_field( $_POST['author_id'] ) : '';
		$active = isset( $_POST['active'] ) ? sanitize_text_field( $_POST['active'] ) : '';

		// some basic validation
		if ( ! $name ) {
			$errors['name'] = __( 'Name is required', TK_LISTING_EXPORTER_NAME );
		}
		if ( ! $secret ) {
			$errors['secret'] = __( 'Secret Key is required', TK_LISTING_EXPORTER_NAME );
		} else if (strlen($secret) > 32) {
			$errors['secret'] = __( 'The Secret Key cannot be longer than 32 characters.', TK_LISTING_EXPORTER_NAME );
		}
		if ( !$author_id === '' || $author_id === null ) {
			$errors['author_id'] = __( 'Listing Author is required', TK_LISTING_EXPORTER_NAME );
		}

		if ( $errors ) {
			foreach ($errors as $k => $v) {
				add_settings_error($k, '', $v, 'error');
			}
			return;
		}

		$fields = array(
			'id' => $field_id,
			'name' => $name,
			'secret' => $secret,
			'author_id' => $author_id,
			'active' => $active,
		);

		// Save Client
		$insert_id = tk_save_ExportClient( $fields );
		if (is_wp_error( $insert_id )) {
			$_SESSION['tk-exporter'] = array('form-errors' => array(
				'Save Error' => implode(', ', $insert_id->get_error_messages())
			));
			$redirect_to = add_query_arg( array( 'message' => 'error' ), $page_url );
		} else {
			$_SESSION['tk-exporter']['form-success'] = true;
			$redirect_to = add_query_arg( array( 'message' => 'success', 'action' => 'edit', 'id' => $insert_id), $page_url );
		}
		wp_safe_redirect( $redirect_to );
		exit;
	}
}

new Form_Handler();
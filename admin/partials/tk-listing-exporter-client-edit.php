<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.tropotek.com/
 * @since      1.0.0
 *
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/admin/partials
 */
?>

<div class="wrap">
  <h2><?php _e( 'Edit Export Client', $this->plugin_name ); ?></h2>

	<?php
    $item = new stdClass();
    $item->id = isset($_POST['id']) ? $_POST['id'] : 0;
    $item->name = isset($_POST['name']) ? $_POST['name'] : '';
    $item->secret = isset($_POST['secret']) ? $_POST['secret'] : '';
    $item->author_id = isset($_POST['author_id']) ? $_POST['author_id'] : 0;
    $item->active = isset($_POST['active']) ? $_POST['active'] : '';
    // Get the record if requested
    if (!empty($_GET['id'])) {
	    $item = tk_get_ExportClient( $_GET['id'] );
    }
  ?>

  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

      <!-- main content -->
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <div class="postbox">
            <h2 class="hndle"><span><?php esc_attr_e( 'Listing Export Client', $this->plugin_name ); ?></span></h2>
            <div class="inside">

  <form action="" method="post">

    <table class="form-table">
      <tbody>
      <tr class="row-name">
        <th scope="row">
          <label for="name"><?php _e( 'Name', $this->plugin_name ); ?></label>
        </th>
        <td>
          <input type="text" name="name" id="name" class="regular-text" placeholder="<?php echo esc_attr( '', $this->plugin_name ); ?>" value="<?php echo esc_attr( $item->name ); ?>" required="required" />
          <p class="description"><?php _e('A name or domain to identify the client', $this->plugin_name ); ?></p>
        </td>
      </tr>
      <tr class="row-secret">
        <th scope="row">
          <label for="secret"><?php _e( 'Secret Key', $this->plugin_name ); ?></label>
        </th>
        <td>
          <input type="text" name="secret" maxlength="32" id="secret" class="regular-text" placeholder="<?php echo esc_attr( '', $this->plugin_name ); ?>" value="<?php echo esc_attr( $item->secret ); ?>" required="required" />
          <p class="description"><?php _e('The client access key, give this to the client so they can import listings', $this->plugin_name ); ?></p>
        </td>
      </tr>
      <tr class="row-author-id">
        <th scope="row">
          <label for="author_id"><?php _e( 'Listing Author', $this->plugin_name ); ?></label>
        </th>
        <td>
          <select name="author_id" id="author_id" required="required">
            <option value="0" <?php selected( $item->author_id, '0' ); ?>><?php echo __( '-- ALL LISTINGS --', $this->plugin_name ); ?></option>
              <?php
                // Create all the options of the listing authors...
                $users = get_users('role=author');
                foreach ($users as $user) {
                    printf('<option value="%s" %s>%s</option>'."\n", $user->data->ID, selected( $item->author_id, $user->data->ID), $user->data->display_name);
                }
              ?>
          </select>
          <p class="description"><?php _e('Select the listings the client can import.', $this->plugin_name ); ?></p>
        </td>
      </tr>$active
      <tr class="row-active">
        <th scope="row">
          <label for="active"><?php _e( 'Active', $this->plugin_name ); ?></label>
        </th>
        <td>
          <input type="checkbox" name="active" id="active" class="regular-text" value="1" <?php echo checked(1, $item->active); ?> />
          <p class="description"><?php _e('Uncheck to disable imports to this client', $this->plugin_name ); ?></p>
        </td>
      </tr>
      </tbody>
    </table>

    <input type="hidden" name="field_id" value="<?php echo $item->id; ?>">

	  <?php wp_nonce_field( 'export-client' ); ?>

    <p>
      <?php
      if (!$item->id) {
      //if ($_GET['action'] == 'add' ) {
	      submit_button( __( 'Create', $this->plugin_name ), 'primary', 'submit-export-client', false );
      } else {
	      submit_button( __( 'Save', $this->plugin_name ), 'primary', 'submit-export-client', false );
      }
      ?>

      <a href="<?php echo admin_url('options-general.php?page=tk-listing-exporter'); ?>" class="button" title=""><?php esc_attr_e('Back', $this->plugin_name); ?></a>
    </p>

  </form>

            </div><!-- .inside -->
          </div><!-- .postbox -->
        </div><!-- .meta-box-sortables .ui-sortable -->
      </div><!-- post-body-content -->

      <!-- sidebar -->
      <div id="postbox-container-1" class="postbox-container">
        <div class="meta-box-sortables">
          <div class="postbox">
            <h2 class="hndle"><span><?php esc_attr_e( 'Actions', $this->plugin_name ); ?></span></h2>
            <div class="inside">

              <p>
                <a href="<?php echo admin_url( 'admin.php?page=tk-listing-export-client-edit&action=add' ); ?>" class="button button-primary">
                   <i class="fa fa-plus"></i> <?php _e( 'Create Export Client', $this->plugin_name ); ?>
                </a>
              </p>

            </div><!-- .inside -->
          </div><!-- .postbox -->
        </div><!-- .meta-box-sortables -->
      </div><!-- #postbox-container-1 .postbox-container -->
    </div><!-- #post-body .metabox-holder .columns-2 -->
    <br class="clear">
  </div>
  <!-- #poststuff -->
</div>



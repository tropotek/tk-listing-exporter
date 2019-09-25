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

  <div id="icon-options-general" class="icon32"></div>
  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

      <!-- main content -->
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <div class="postbox">
            <h2 class="hndle"><span><?php esc_attr_e( 'Listing Export Clients', $this->plugin_name ); ?></span></h2>
            <div class="inside">

              <form method="post">
                <input type="hidden" name="page" value="ttest_list_table">

                <?php
                $list_table = new Tk_Listing_Export_Client();
                $list_table->prepare_items();
                $list_table->search_box( 'search', 'search_id' );
                $list_table->display();
                ?>

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

</div> <!-- .wrap -->

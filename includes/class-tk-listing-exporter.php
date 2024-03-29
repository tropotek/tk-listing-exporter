<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.tropotek.com/
 * @since      1.0.0
 *
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tk_Listing_Exporter
 * @subpackage Tk_Listing_Exporter/includes
 * @author     Mick Mifsud <info@tropotek.com>
 */
class Tk_Listing_Exporter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tk_Listing_Exporter_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TK_LISTING_EXPORTER_VERSION' ) ) {
			$this->version = TK_LISTING_EXPORTER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		if ( defined( 'TK_LISTING_EXPORTER_NAME' ) ) {
			$this->plugin_name = TK_LISTING_EXPORTER_NAME;
		} else {
			$this->plugin_name = 'tk-listing-exporter';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tk_Listing_Exporter_Loader. Orchestrates the hooks of the plugin.
	 * - Tk_Listing_Exporter_i18n. Defines internationalization functionality.
	 * - Tk_Listing_Exporter_Admin. Defines all hooks for the admin area.
	 * - Tk_Listing_Exporter_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tk-listing-exporter-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tk-listing-exporter-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tk-listing-exporter-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tk-listing-exporter-public.php';

		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/tk-listing-export-functions.php';

		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-form-handler.php';

        require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-tk-export.php';

		$this->loader = new Tk_Listing_Exporter_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tk_Listing_Exporter_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tk_Listing_Exporter_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

        if (WP_DEBUG)
            remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tk_Listing_Exporter_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Save/Update our plugin options
		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

//		$plugin_public = new Tk_Listing_Exporter_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $this, 'init_exporter');

	}

	public function init_exporter()
	{
        if (isset($_GET['tk-listing-exporter']) ) {
            $errorMsg = 'Unknown Export Error';
            // Find the client record
            $secret = !empty($_GET['tk-listing-exporter']) ? $_GET['tk-listing-exporter'] : '';
            if ($secret) {
                $client = tk_get_by_secret_ExportClient($this->decrypt(urldecode($secret)));
                if ($client && $client->active) {
                    $exporter = new Tk_Export();
                    $args = array(
                        'content' => 'listings',
                        'status' => 'publish'
                    );
                    if ($client->author_id)
                        $args['author'] = (int)$client->author_id;
                    $args = apply_filters( 'export_args', $args );
                    //error_log(print_r($_GET, true));
                    if (isset($_GET['zip'])) {
                        $err = $this->getZip($args, $exporter, $client);
                    } else {
                        $err = $this->getXml($args, $exporter);
                    }
                    if (!count($err)) {
                        //  Update client export timestamp
                        $now = new DateTime('now');
                        $client->last_cache = $now->format('Y-m-d H:i:s');
                        tk_save_ExportClient((array)$client);
                    }
                    exit();
                }
                $errorMsg = 'Error: Contact your listing administrator as your account is not active.';
            }
            status_header(500);
            echo $errorMsg;
            exit();
        }
	}

    /**
     * @param array $args
     * @param Tk_Export $exporter
     * @return array
     */
	protected function getXml($args, $exporter)
    {
        $err = array();
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        $xml = $exporter->export($args);
        echo $xml;
        return $err;
    }

    /**
     * @param array $args
     * @param Tk_Export $exporter
     * @param array $client
     * @return array
     */
    protected function getZip($args, $exporter, $client)
    {
        $err = array();
        $total = $this->getListingsCount($args);
        $limit = 10;
        $pages = floor($total/$limit);

        // save all XML`s to text files
        $tmpDir =  ABSPATH . 'wp-content/uploads/tk-exporter/export-' . $client->author_id;
        if (is_dir($tmpDir)) $this->rrmdir($tmpDir);
        @mkdir($tmpDir, 0700, true);

        $zipfile = $tmpDir . '/export.zip';
        $zip = new ZipArchive();
        if($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            $errMsg = "Could not Create $zipfile";
            $err[] = $errMsg;
            error_log($errMsg);
            return $err;
        }

        for($i = 0; $i<=$pages;$i++) {
            $offset = ($i*($limit));
            if (WP_DEBUG) {
                $offsetEnd = ($limit + ($limit * $i));
                if ($offsetEnd > $total) $offsetEnd = $total;
                error_log( '[' . memory_get_usage(). '] Process: ' . $offset . ' - ' . $offsetEnd);
            }

            $args['limit'] = $limit;
            $args['offset'] = $offset;
            $xml = $exporter->export($args);
            $filename = $tmpDir . '/export-' . $i . '.xml';
            file_put_contents($filename, $xml);
            $zip->addFile($filename, 'export-' . $i . '.xml');
            unset($xml);
            gc_collect_cycles();
        }

        $zip->close();

        // TODO: stream the new zip file
        header( "Content-Type: application/x-zip" );
        header( "Content-Disposition: attachment; filename=\"tk-export-file.zip\"" );
        @readfile($zipfile);

        // delete folder
        $this->rrmdir($tmpDir);

        return $err;
    }





	protected function getListingsCount($args)
    {
        global $wpdb;
        $where = "wp_posts.post_type = 'listings' AND wp_posts.post_status = 'publish' ";
        if ($args['status'] && ('listings' == $args['content'] || 'post' == $args['content'] || 'page' == $args['content'])) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_status = %s", $args['status']);
        } else {
            $where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";
        }
        if (isset($args['author'])) {
                $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_author = %d", $args['author']);
        }
        $sql = "SELECT *
FROM wp_posts
WHERE $where ";
        $post_ids = $wpdb->get_col($sql);

        return count($post_ids);
    }


    public function decrypt($crypted_token)
    {
        $crypted_token = base64_decode($crypted_token);
        list($crypted_token, $enc_iv) = explode("::", $crypted_token);;
        $cipher_method = 'aes-128-ctr';
        $enc_key = openssl_digest(TK_LISTING_EXPORTER_URL_KEY, 'SHA256', TRUE);
        $token = openssl_decrypt($crypted_token, $cipher_method, $enc_key, 0, hex2bin($enc_iv));
        unset($crypted_token, $cipher_method, $enc_key, $enc_iv);
        return $token;
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tk_Listing_Exporter_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }

}

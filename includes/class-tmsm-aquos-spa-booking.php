<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
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
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquos_Spa_Booking {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tmsm_Aquos_Spa_Booking_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'TMSM_AQUOS_SPA_BOOKING_VERSION' ) ) {
			$this->version = TMSM_AQUOS_SPA_BOOKING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tmsm-aquos-spa-booking';

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
	 * - Tmsm_Aquos_Spa_Booking_Loader. Orchestrates the hooks of the plugin.
	 * - Tmsm_Aquos_Spa_Booking_i18n. Defines internationalization functionality.
	 * - Tmsm_Aquos_Spa_Booking_Admin. Defines all hooks for the admin area.
	 * - Tmsm_Aquos_Spa_Booking_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquos-spa-booking-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquos-spa-booking-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tmsm-aquos-spa-booking-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tmsm-aquos-spa-booking-public.php';

		$this->loader = new Tmsm_Aquos_Spa_Booking_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tmsm_Aquos_Spa_Booking_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tmsm_Aquos_Spa_Booking_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tmsm_Aquos_Spa_Booking_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );


		// Order
		//$this->loader->add_action( 'woocommerce_hidden_order_itemmeta', $plugin_admin, 'woocommerce_hidden_order_itemmeta', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_item_get_formatted_meta_data', $plugin_admin, 'woocommerce_order_item_get_formatted_meta_data', 10, 2 );

		// WooCommerce settings
		$this->loader->add_filter( 'woocommerce_get_settings_pages', $plugin_admin, 'woocommerce_get_settings_pages_aquosspabooking' );

		$this->loader->add_action( 'woocommerce_product_options_inventory_product_data', $plugin_admin, 'woocommerce_product_options_inventory_product_data_aquosid' );

		$this->loader->add_action( 'woocommerce_process_product_meta_simple', $plugin_admin, 'woocommerce_process_product_save_options' );
		$this->loader->add_action( 'woocommerce_process_product_meta_variable', $plugin_admin, 'woocommerce_process_product_save_options' );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'woocommerce_save_product_variation', 10, 2 );
		$this->loader->add_action( 'woocommerce_variation_options_pricing', $plugin_admin, 'woocommerce_variation_options_pricing', 10, 3 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tmsm_Aquos_Spa_Booking_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 100 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 100 );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'product_category_template' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'product_template' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'product_variation_template' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'time_template' );

		// WooCommerce
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'woocommerce_add_cart_item_data_appointment', 10, 3 );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'woocommerce_get_item_data_appointment', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'woocommerce_get_item_data_appointment', 10, 2 );
		$this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $plugin_public, 'woocommerce_checkout_create_order_line_item_appointment', 10, 4 );
		$this->loader->add_filter( 'woocommerce_display_item_meta', $plugin_public, 'woocommerce_display_item_meta_appointment', 10, 3 );
		$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $plugin_public, 'woocommerce_thankyou_order_received_text_appointment', 100, 2 );
		$this->loader->add_action( 'woocommerce_email_before_order_table', $plugin_public, 'woocommerce_email_before_order_table_appointment', 20, 4 );
		$this->loader->add_action( 'woocommerce_check_cart_items', $plugin_public, 'woocommerce_check_cart_items_expire' );
		$this->loader->add_filter( 'woocommerce_cod_process_payment_order_status', $plugin_public, 'woocommerce_cod_process_payment_order_status', 20, 1 );
		$this->loader->add_filter( 'woocommerce_order_button_text', $plugin_public, 'woocommerce_order_button_text', 20, 1 );

		// Ajax
		$this->loader->add_action( 'wp_ajax_tmsm-aquos-spa-booking-product-categories', $plugin_public, 'ajax_product_categories' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquos-spa-booking-product-categories', $plugin_public, 'ajax_product_categories' );

		$this->loader->add_action( 'wp_ajax_tmsm-aquos-spa-booking-products', $plugin_public, 'ajax_products' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquos-spa-booking-products', $plugin_public, 'ajax_products' );

		$this->loader->add_action( 'wp_ajax_tmsm-aquos-spa-booking-variations', $plugin_public, 'ajax_product_variations' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquos-spa-booking-variations', $plugin_public, 'ajax_product_variations' );

		$this->loader->add_action( 'wp_ajax_tmsm-aquos-spa-booking-times', $plugin_public, 'ajax_times' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquos-spa-booking-times', $plugin_public, 'ajax_times' );

		$this->loader->add_action( 'wp_ajax_tmsm-aquos-spa-booking-addtocart', $plugin_public, 'ajax_addtocart' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquos-spa-booking-addtocart', $plugin_public, 'ajax_addtocart' );

		// Override Cart/Checkout
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'woocommerce_before_calculate_totals_appointment' );
		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $plugin_public, 'woocommerce_available_payment_gateways_cashondelivery', 10, 3 );

		$this->loader->add_filter( 'the_title', $plugin_public, 'the_title', 10, 2 );
		$this->loader->add_filter( 'body_class', $plugin_public, 'body_class', 10, 2 );

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
	 * @return    Tmsm_Aquos_Spa_Booking_Loader    Orchestrates the hooks of the plugin.
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

}

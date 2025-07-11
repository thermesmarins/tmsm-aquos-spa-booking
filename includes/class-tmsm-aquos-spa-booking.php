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
class Tmsm_Aquos_Spa_Booking
{

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
	public function __construct()
	{
		if (defined('TMSM_AQUOS_SPA_BOOKING_VERSION')) {
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
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-loader.php';

		/**
		 * The class responsible for orchestrating the background process actions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-backgroundprocess.php';


		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tmsm-aquos-spa-booking-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-tmsm-aquos-spa-booking-public.php';

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
	private function set_locale()
	{

		$plugin_i18n = new Tmsm_Aquos_Spa_Booking_i18n();

		$this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Tmsm_Aquos_Spa_Booking_Admin($this->get_plugin_name(), $this->get_version());



		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// WooCommerce Order Status
		$this->loader->add_filter('woocommerce_register_shop_order_post_statuses', $plugin_admin, 'order_status_appointment_register', 20);
		$this->loader->add_filter('wc_order_statuses', $plugin_admin, 'order_status_appointment_name', 20, 1);

		// WooCommerce Order
		$this->loader->add_action('woocommerce_order_item_get_formatted_meta_data', $plugin_admin, 'woocommerce_order_item_get_formatted_meta_data', 10, 2);
		$this->loader->add_filter('woocommerce_hidden_order_itemmeta', $plugin_admin, 'woocommerce_hidden_order_itemmeta_appointment', 20, 1);

		// WooCommerce Settings
		$this->loader->add_filter('woocommerce_get_settings_pages', $plugin_admin, 'woocommerce_get_settings_pages');

		// WooCommerce Aquos ID Field + Aquos Choice Field
		$this->loader->add_action('woocommerce_product_options_inventory_product_data', $plugin_admin, 'woocommerce_product_options_inventory_product_data_fields');
		$this->loader->add_action('woocommerce_process_product_meta_simple', $plugin_admin, 'woocommerce_process_product_save_options');
		$this->loader->add_action('woocommerce_process_product_meta_variable', $plugin_admin, 'woocommerce_process_product_save_options');
		$this->loader->add_action('woocommerce_save_product_variation', $plugin_admin, 'woocommerce_save_product_variation', 10, 2);
		$this->loader->add_action('woocommerce_variation_options_pricing', $plugin_admin, 'woocommerce_variation_options_pricing', 10, 3);
		// $this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $plugin_admin, 'woocommerce_admin_order_data_after_order_details_appointment_id', 10, 3 );

		// Regenerate Aquos Prices
		$this->loader->add_action('tmsm_aquos_spa_booking_cronaction', $plugin_admin, 'regenerate_aquos_prices', 10, 1);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Tmsm_Aquos_Spa_Booking_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles', 100);
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 100);

		$this->loader->add_action('init', $plugin_public, 'register_shortcodes');

		// Bookable meta field
		$this->loader->add_filter('woocommerce_product_data_store_cpt_get_products_query', $plugin_public, 'woocommerce_product_data_store_cpt_get_products_query_bookable', 10, 3);

		// Html Templates
		$this->loader->add_action('wp_footer', $plugin_public, 'template_havevoucher');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_product_category');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_product');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_product_variation');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_product_attribute');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_weekday');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_time');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_course_time');
		$this->loader->add_action('wp_footer', $plugin_public, 'template_choice');

		// Cart Items
		$this->loader->add_filter('woocommerce_is_purchasable', $plugin_public, 'woocommerce_is_purchasable_appointment', 20, 2);
		$this->loader->add_filter('woocommerce_variation_is_purchasable', $plugin_public, 'woocommerce_is_purchasable_appointment', 20, 2);
		$this->loader->add_filter('woocommerce_add_cart_item_data', $plugin_public, 'woocommerce_add_cart_item_data_appointment', 10, 3);
		$this->loader->add_action('woocommerce_after_cart_table', $plugin_public, 'remove_checkout_button', 10, 0);
		$this->loader->add_filter('woocommerce_add_to_cart_validation', $plugin_public, 'woocommerce_add_to_cart_validation', 10, 6);
		$this->loader->add_filter('woocommerce_get_item_data', $plugin_public, 'woocommerce_get_item_data_appointment', 10, 2);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'woocommerce_checkout_create_order_line_item_appointment', 20, 4);
		$this->loader->add_filter('woocommerce_display_item_meta', $plugin_public, 'woocommerce_display_item_meta_appointment', 10, 3);
		$this->loader->add_action('woocommerce_check_cart_items', $plugin_public, 'woocommerce_check_cart_items', -100);

		// Confirmation Page/Emails
		$this->loader->add_filter('woocommerce_thankyou_order_received_text', $plugin_public, 'woocommerce_thankyou_order_received_text_appointment', 100, 2);
		$this->loader->add_action('woocommerce_before_thankyou', $plugin_public, 'woocommerce_thankyou_order_error', 0, 1);
		$this->loader->add_action('woocommerce_email_before_order_table', $plugin_public, 'woocommerce_email_before_order_table_appointment', 20, 4);

		// Virtual only Column
		//$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_public, 'shop_order_posts_custom_column_appointment', 50, 2 );

		// Checkout Page Customization
		$this->loader->add_filter('the_title', $plugin_public, 'the_title', 10, 2);
		$this->loader->add_filter('woocommerce_endpoint_order-received_title', $plugin_public, 'woocommerce_endpoint_order_received_title', 50, 2);
		$this->loader->add_filter('body_class', $plugin_public, 'body_class', 10, 2);
		$this->loader->add_filter('woocommerce_order_button_text', $plugin_public, 'woocommerce_order_button_text', 20, 1);

		// Get Data on Appointment Page
		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-product-categories', $plugin_public, 'ajax_product_categories');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-product-categories', $plugin_public, 'ajax_product_categories');

		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-products', $plugin_public, 'ajax_products');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-products', $plugin_public, 'ajax_products');

		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-attributes', $plugin_public, 'ajax_product_attributes');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-attributes', $plugin_public, 'ajax_product_attributes');

		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-variations', $plugin_public, 'ajax_product_variations');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-variations', $plugin_public, 'ajax_product_variations');

		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-times', $plugin_public, 'ajax_times');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-times', $plugin_public, 'ajax_times');

		$this->loader->add_action('wp_ajax_tmsm-aquos-spa-booking-addtocart', $plugin_public, 'ajax_addtocart');
		$this->loader->add_action('wp_ajax_nopriv_tmsm-aquos-spa-booking-addtocart', $plugin_public, 'ajax_addtocart');


		$this->loader->add_action('woocommerce_checkout_before_order_review_heading', $plugin_public, 'woocommerce_checkout_before_order_review_heading', 200);

		$this->loader->add_filter('woocommerce_billing_fields', $plugin_public, 'billing_fields_phone', 10, 1);

		// Checkout: set virtual, and set payments gateways
		$this->loader->add_action('woocommerce_before_calculate_totals', $plugin_public, 'woocommerce_before_calculate_totals_appointment', 20);
		$this->loader->add_filter('woocommerce_cart_needs_shipping', $plugin_public, 'woocommerce_cart_needs_shipping', 10, 1);
		$this->loader->add_filter('woocommerce_available_payment_gateways', $plugin_public, 'woocommerce_available_payment_gateways_cashondelivery', 10, 3);
		$this->loader->add_filter('woocommerce_cod_process_payment_order_status', $plugin_public, 'woocommerce_cod_process_payment_order_status', 20, 1);
		$this->loader->add_filter('woocommerce_order_item_needs_processing', $plugin_public, 'woocommerce_order_item_needs_processing', 20, 3);
		$this->loader->add_action('woocommerce_after_cart_item_quantity_update', $plugin_public, 'woocommerce_after_cart_item_quantity_update', 20, 4);
		$this->loader->add_filter('woocommerce_cart_crosssell_ids', $plugin_public, 'filter_woocommerce_cart_crosssell_ids', 10, 2);


		// Order Status Appointment for COD (not free)
		$this->loader->add_filter('woocommerce_cod_process_payment_order_status', $plugin_public, 'order_status_appointment_cod', 20, 2);
		// Order Status Appointment for Voucher (free)
		$this->loader->add_filter('woocommerce_payment_complete_order_status', $plugin_public, 'order_status_appointment_voucher', 600, 3);

		$this->loader->add_action('woocommerce_order_status_appointment', $plugin_public, 'order_status_changed_to_appointment', 80, 2);

		$this->loader->add_action('woocommerce_checkout_create_order_fee_item', $plugin_public, 'order_fee_item_metadata_giftwrap', 80, 4);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'create_order_line_item_with_coupon', 50, 4);

		// Appointment public cancelation
		$this->loader->add_filter('woocommerce_valid_order_statuses_for_cancel', $plugin_public, 'add_woocommerce_valid_order_statuses_for_cancel_filter', 90, 2);
		// $this->loader->add_action('woocommerce_order_details_after_order_table', $plugin_public, 'add_woocommerce_order_details_after_order_table', 50, 2);
		// $this->loader->add_action( 'woocommerce_cancelled_order', $plugin_public, 'cancel_notification', 50, 2);
		$this->loader->add_action('woocommerce_order_status_changed', $plugin_public, 'appointment_order_status_changed_to_canceled', 10, 3);

		// Appointment link
		$this->loader->add_action('woocommerce_single_product_summary', $plugin_public, 'woocommerce_single_product_summary_appointmentlink', 30);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tmsm_Aquos_Spa_Booking_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}

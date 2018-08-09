<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/admin
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquos_Spa_Booking_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquos-spa-booking-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquos-spa-booking-admin.js', array( 'jquery' ), $this->version, true );

	}

	/**
	 * Displays recipient item meta on order page
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @param mixed         $formatted_meta
	 * @param WC_Order_Item $order_item
	 *
	 * @return mixed
	 */
	public function woocommerce_order_item_get_formatted_meta_data( $formatted_meta, WC_Order_Item $order_item ) {
		if ( empty( $formatted_meta ) ) {
			return $formatted_meta;
		}

		foreach ( $formatted_meta as $meta ) {

			if($meta->key == '_appointment' && !empty($meta->value)){
				$meta->display_key = __('Appointment', 'tmsm-aquos-spa-booking');
			}

		}

		return $formatted_meta;
	}
}

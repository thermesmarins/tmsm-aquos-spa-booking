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
			if ( $meta->key == '_has_voucher' ) {
				$meta->display_key = __('Has Voucher', 'tmsm-aquos-spa-booking');
				$meta->display_value = ( $meta->value ? __( 'Yes', 'tmsm-aquos-spa-booking' ) : __( 'No', 'tmsm-aquos-spa-booking' ) );
			}
			if($meta->key == '_aquos_id' && !empty($meta->value)){
				$meta->display_key = __('Aquos ID', 'tmsm-aquos-spa-booking');
			}

		}

		return $formatted_meta;
	}

	/**
	 * Hide order item meta from Order
	 *
	 * @since 1.0.0
	 *
	 * @param array $item_array
	 *
	 * @return array $item_array
	 */
	public function woocommerce_hidden_order_itemmeta_appointment($item_meta){
		$item_meta[] = '_appointment_date';
		$item_meta[] = '_appointment_time';

		return $item_meta;
	}

	/**
	 * Include settings class
	 *
	 * @since 1.0.0
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	function woocommerce_get_settings_pages_aquosspabooking($settings) {
		$settings[] = include( plugin_dir_path( dirname( __FILE__ ) ) .'includes/class-tmsm-aquos-spa-booking-settings.php' );
		return $settings; // Return
	}

	/**
	 * Add Aquos ID Field to Inventory Product Data Tab
	 */
	public function woocommerce_product_options_inventory_product_data_aquosid(){
		echo '<div class="options_group">';
		woocommerce_wp_text_input(
			array(
				'id'          => '_aquos_id',
				'label'       => __( 'Aquos Product ID', 'tmsm-aquos-spa-booking' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'If empty, the product won\'t be bookable.', 'tmsm-aquos-spa-booking' )
			)
		);
		echo '</div>';
	}

	/**
	 * Save Product Variation Data
	 *
	 * @since 1.0.0
	 *
	 * @param $variation_id
	 *
	 * @return void
	 */
	function woocommerce_save_product_variation( $variation_id, $i ) {

		if ( isset( $_POST['_aquos_id'][$i] ) ) :
			update_post_meta( $variation_id, '_aquos_id', sanitize_text_field( $_POST['_aquos_id'][$i] ) );
		endif;

	}

	/**
	 * Save Product Data
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	function woocommerce_process_product_save_options( $post_id ) {

		if ( isset( $_POST['_aquos_id'] ) ) :
			update_post_meta( $post_id, '_aquos_id', sanitize_text_field( $_POST['_aquos_id'] ) );
		endif;

	}

	/**
	 * Add Aquos ID Field to Product Variation
	 *
	 * @param int     $loop           Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Post data.
	 */
	function woocommerce_variation_options_pricing( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
				'id' => '_aquos_id[' . $loop . ']',
				'wrapper_class' => 'form-row',
				'label' => __( 'Aquos Product ID', 'tmsm-aquos-spa-booking' ),
				'value' => get_post_meta( $variation->ID, '_aquos_id', true )
			)
		);
	}
}

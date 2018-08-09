<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/thermesmarins/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/includes
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WC_Settings_Aquosspabooking' ) ) :
		/**
		 * Settings class
		 *
		 * @since 1.0.0
		 */
		class WC_Settings_Aquosspabooking extends WC_Settings_Page {

			/**
			 * Setup settings class
			 *
			 * @since  1.0
			 */
			public function __construct() {

				$this->id    = 'tmsmaquosspabooking';
				$this->label = __( 'Aquos Spa Booking', 'tmsm-aquos-spa-booking' );

				add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
				add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
				add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
				add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
			}

			/**
			 * Get sections
			 *
			 * @return array
			 */
			public function get_sections() {

				$sections = array(
					''         => __( 'Voucher settings', 'tmsm-aquos-spa-booking' ),
				);

				return $sections;
			}

			/**
			 * Get settings array
			 *
			 * @since 1.0.0
			 * @param string $current_section Optional. Defaults to empty string.
			 * @return array Array of settings
			 */
			public function get_settings( $current_section = '' ) {

				$settings = array(

					array(
						'name' => __( 'Aquos Spa Booking Settings', 'tmsm-aquos-spa-booking' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'tmsm_aquos_spa_booking_settings',
					),

					array(
						'type'     => 'number',
						'id'       => 'tmsm_aquos_spa_booking_productcat',
						'name'     => __( 'WooCommerce Product Category ID', 'tmsm-aquos-spa-booking' ),
						'desc'     => ''.
						              '<p class="description">'.
						              __( 'The ID of the product category. Leave 0 if all categories can be booked.', 'tmsm-aquos-spa-booking'). '</p>',
						'default' => 0,
						'class'  => 'regular-text',
						'required' => true
					),

					array(
						'type'     => 'checkbox',
						'id'       => 'tmsm_aquos_spa_booking_acceptcashondelivery',
						'name'     => __( 'Accept Cash On Delivery', 'tmsm-woocommerce-vouchers' ),
						'desc'     => '<p class="description">'.__( 'Accept Cash On Delivery for the bookable products. Note: if cart has other types of products, the default methods will apply.' ).'</p>',
						'default'  => 'yes',
					),

					array(
						'type'     => 'checkbox',
						'id'       => 'tmsm_aquos_spa_booking_acceptonlinepayment',
						'name'     => __( 'Accept Online Payment', 'tmsm-woocommerce-vouchers' ),
						'desc'     => '<p class="description">'.__( 'Accept Online Payment for the Bookable Products.' ).'</p>',
						'default'  => 'no',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'tmsm_woocommerce_vouchers_settings'
					),

				) ;

				return $settings;

			}

			/**
			 * Output the settings
			 *
			 * @since 1.0
			 */
			public function output() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::output_fields( $settings );
			}

			/**
			 * Save settings
			 *
			 * @since 1.0
			 */
			public function save() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
			}
		}


endif;
return new WC_Settings_Aquosspabooking();
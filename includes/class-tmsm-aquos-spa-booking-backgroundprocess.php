<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

require( 'vendors/wp-async-request.php' );
require( 'vendors/wp-background-process.php' );

/**
 * Extends the background process class
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Tmsm_Aquos_Spa_Booking_Background_Process extends WP_Background_Process {

	/**
	 * Specific action identifier
	 *
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'tmsm_asb_bp';

	/**
	 * Initiate new background process.
	 */
	public function __construct() {

		$this->prefix = 'wp_' . get_current_blog_id();

		parent::__construct();
	}

	/**
	 * Task
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return bool|mixed
	 * @throws Exception
	 */
	protected function task( $item ) {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('Tmsm_Aquos_Spa_Booking_Background_Process task:');
			error_log(print_r($item, true));
		}

		$order_id = $item['order_id'];

		$order = wc_get_order($order_id);


		if ( ! empty( $order ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Tmsm_Aquos_Spa_Booking_Background_Process is order');
			}

			foreach ( $order->get_items() as $order_item_id => $order_item_data) {

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item_data:');
					error_log(print_r($order_item_data, true));
				}

				// Only items with an appointment
				if(!empty($order_item_data['_appointment'])){

					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is appointment');
					}

					// If not aready processed, processing
					if ( wc_get_order_item_meta( $order_item_id, '_appointment_processed', true ) !== 'yes' ) {

						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
							error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is not processed');
						}

						// Call web service
						$settings_webserviceurl = get_option( 'tmsm_aquos_spa_booking_webserviceurlsubmit' );
						if ( ! empty( $settings_webserviceurl ) ) {

							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								error_log( 'url before:' . $settings_webserviceurl );
							}

							$patterns     = [
								'/{date}/',
								'/{product_id}/',
								'/{site_id}/',
								'/{time}/',
								'/{title}/',
								'/{firstname}/',
								'/{lastname}/',
								'/{email}/',
								'/{has_voucher}/',
								'/{birthdate}/',
								'/{address}/',
								'/{postalcode}/',
								'/{city}/',
								'/{phone}/',
								'/{notes}/',
								'/{site_name}/'
							];

							$replacements = [
								esc_html( str_replace( '-', '', $order_item_data['_appointment_date'] ) ),
								$order_item_data['_aquos_id'],
								( is_multisite() ? get_current_blog_id() : 0 ),
								$order_item_data['_appointment_time'],
								$order->get_meta('billing_title') == '1' ? 'M.' : 'Mme',
								$order->get_billing_first_name() ?? '""',
								$order->get_billing_last_name() ?? '""',
								$order->get_billing_email() ?? '""',
								$order_item_data['_has_voucher'] ?? '0',
								$order->get_meta('billing_birthdate') ?? '""',
								$order->get_billing_address_1(). ' '.$order->get_billing_address_2(),
								$order->get_billing_postcode() ?? '""',
								$order->get_billing_city() ?? '""',
								$order->get_billing_phone() ?? '""',
								$order->get_customer_note() ?? '""',
								esc_html( get_bloginfo( 'name' ) ) ?? '""',
							];

							// Replace keywords in url
							$settings_webserviceurl = preg_replace( $patterns, $replacements, $settings_webserviceurl );
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								error_log( 'url after:' . $settings_webserviceurl );
							}

							// Connect with cURL
							$ch = curl_init();
							curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
							curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
							curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
							$result = curl_exec( $ch );
							curl_close( $ch );

							if(empty($result)){
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
									error_log('Web service is not available');
								}
								$errors[] = __( 'Web service is not available', 'tmsm-aquos-spa-booking' );
							}
							else{
								$result_array = json_decode( $result, true );

								if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
									error_log('Webservice response');
									error_log( var_export( $result_array, true ) );
								}

								if(!empty($result_array['Status']) && $result_array['Status'] == 'true'){
									// no errors
									wc_update_order_item_meta($order_item_id, '_appointment_processed', 'yes');
									if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
										error_log('Web service submission successful');
									}

								}
								else{
									if(!empty($result_array['ErrorCode']) && !empty($result_array['ErrorMessage'])){
										if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
											error_log(sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $result_array['ErrorCode'], $result_array['ErrorMessage']));
										}
										$errors[] = sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $result_array['ErrorCode'], $result_array['ErrorMessage']);
									}
									else{
										if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
											error_log('Unknown error');
										}
										$errors[] = __( 'Unknown error', 'tmsm-aquos-spa-booking' );
									}
								}
							}
						}

					}
				}

			}
		}

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		error_log('Tmsm_Aquos_Spa_Booking_Background_Process complete');
		parent::complete();
	}

}
$GLOBALS['tmsm_asb_bp'] = new Tmsm_Aquos_Spa_Booking_Background_Process();
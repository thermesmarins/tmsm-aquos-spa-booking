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

							$appointment_date = esc_html( str_replace( '-', '', trim($order_item_data['_appointment_date'] )) );
							$appointment_time = esc_html($order_item_data['_appointment_time']);

							$replacements = [
								$appointment_date,
								esc_html(trim($order_item_data['_aquos_id'])),
								( is_multisite() ? get_current_blog_id() : 0 ),
								esc_html($order_item_data['_appointment_time']),
								urlencode($order->get_meta('_billing_title') == '1') ? 'M.' : 'Mme',
								urlencode(esc_html(trim($order->get_billing_first_name()))) ?? '',
								urlencode(esc_html(trim($order->get_billing_last_name()))) ?? '',
								urlencode(esc_html(trim($order->get_billing_email()))) ?? '',
								$order_item_data['_has_voucher'] ?? '0',
								str_replace( '-', '', $order->get_meta( '_billing_birthdate' ) ) ?? '',
								urlencode(esc_html(trim($order->get_billing_address_1(). ' '.$order->get_billing_address_2()))),
								esc_html(trim($order->get_billing_postcode())) ?? '',
								urlencode(esc_html(trim($order->get_billing_city()))) ?? '',
								urlencode(esc_html(trim($order->get_billing_phone()))) ?? '',
								urlencode(esc_html(trim($order->get_customer_note())) ?? ''),
								urlencode(esc_html( get_bloginfo( 'name' ) )) ?? '',
							];

							// Replace keywords in url
							$settings_webserviceurl = preg_replace( $patterns, $replacements, $settings_webserviceurl );
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								error_log( 'url after:' . $settings_webserviceurl );
							}
							$settings_webserviceurl.='///';

							// Connect with cURL
							$ch = curl_init();
							curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
							curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
							curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
							$result = curl_exec( $ch );
							$errors = [];
							$result_array = [];

							if(empty($result)){
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
									error_log('Web service is not available');
								}
								$errors[] = __( 'Web service is not available', 'tmsm-aquos-spa-booking' );
							}
							else{
								$result_array = json_decode( $result, true );

								// Debug response
								if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
									error_log('Webservice response');
									error_log( var_export( $result_array, true ) );
									error_log( print_r( curl_getinfo($ch), true ) );
								}

								// No errors, success
								if(!empty($result_array['Status']) && $result_array['Status'] == 'true'){
									wc_update_order_item_meta($order_item_id, '_appointment_processed', 'yes');
									if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
										error_log('Web service submission successful');
									}

								}

								// Some error detected
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
							curl_close( $ch );


							// Notify admin if errors
							if(!empty($errors)) {

								wc_update_order_item_meta($order_item_id, '_appointment_processed', 'no');
								update_post_meta($order_id, '_appointment_error', 'yes');

								$blogname = esc_html( get_bloginfo( 'name' ) );
								$email    = stripslashes( get_site_option( 'admin_email' ) );
								$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking submission error %s', 'tmsm-aquos-spa-booking' ), $blogname, $result_array['ErrorCode'] ?? __( 'Unknown error', 'tmsm-aquos-spa-booking' ));

								$message  = sprintf(__( 'Customer %s ', 'tmsm-aquos-spa-booking' ), $order->get_formatted_billing_full_name());
								$message .= "\r\n";
								$message .= sprintf(__( 'Address: %s ', 'tmsm-aquos-spa-booking' ), $order->get_formatted_billing_address());
								$message .= "\r\n";
								$message .= sprintf(__( 'Email: %s ', 'tmsm-aquos-spa-booking' ), $order->get_billing_email());
								$message .= "\r\n\r\n";
								$message .= sprintf(__( 'Treatment: %s', 'tmsm-aquos-spa-booking' ), $order_item_data);
								$message .= "\r\n";
								$message .= sprintf(__( 'Appointment: %s at %s', 'tmsm-aquos-spa-booking' ), $appointment_date, $appointment_time);
								$message .= "\r\n";
								$message .= sprintf(__( 'URL called: %s', 'tmsm-aquos-spa-booking' ), $settings_webserviceurl);
								$message .= "\r\n";
								$message .= sprintf(__( 'Errors: %s', 'tmsm-aquos-spa-booking' ), implode(', ', $errors));

								$headers = 'Auto-Submitted: auto-generated';
								wp_mail( $email, $subject, $message, $headers );

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
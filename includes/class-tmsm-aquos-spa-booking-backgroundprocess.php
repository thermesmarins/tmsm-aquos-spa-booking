<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

require( 'vendors/wp-async-request.php' );
require( 'vendors/wp-background-process.php' );
if(! defined('TMSM_AQUOS_SPA_BOOKING_TEMPLATES')){
	define( 'TMSM_AQUOS_SPA_BOOKING_TEMPLATES', plugin_dir_path( __FILE__ ) );
}
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

	/*
	 * For logging purposes
	 */
	private $logger = false;

	/**
	 * Initiate new background process.
	 */
	public function __construct() {



		$this->prefix = 'wp_' . get_current_blog_id();

		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ), 90, 1 );

		add_action( 'woocommerce_order_action_send_appointment_confirmation', array( $this, 'trigger' ) );

		parent::__construct();
	}


	public function trigger( $order_id ) {

		$email_classes  = WC()->mailer()->emails;

		$email_appointment = $email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment'];
		$email_appointment->trigger($order_id);


	}

	/**
	 * @param array $email_classes
	 *
	 * @return array
	 */
	public function register_email( $email_classes ) {
		$email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment'] = require_once(plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquos-spa-booking-email-appointment.php');

		return $email_classes;
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

		if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
			error_log('Tmsm_Aquos_Spa_Booking_Background_Process task:');
			error_log(print_r($item, true));
		}

		$order_id = $item['order_id'];

		$order = wc_get_order($order_id);


		if ( ! empty( $order ) ) {
			if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
				error_log('Tmsm_Aquos_Spa_Booking_Background_Process is order');
			}

			foreach ( $order->get_items() as $order_item_id => $order_item_data) {

				if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
					error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item_data:');
					error_log(print_r($order_item_data, true));
				}

				// Only items with an appointment
				if(!empty($order_item_data['_appointment'])){

					if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
						error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is appointment');
					}

					// If not aready processed, processing
					if ( wc_get_order_item_meta( $order_item_id, '_appointment_processed', true ) !== 'yes' ) {

						if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
							error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is not processed');
						}

						// Call web service
						$settings_webserviceurl = get_option( 'tmsm_aquos_spa_booking_webserviceurlsubmit' );
						if ( ! empty( $settings_webserviceurl ) ) {

							if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
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

							$appointment_date = sanitize_text_field( str_replace( '-', '', trim($order_item_data['_appointment_date'] )) );
							$appointment_time = sanitize_text_field($order_item_data['_appointment_time']);

							$ignoredproducts_array = explode(',', get_option( 'tmsm_aquos_spa_booking_ignoredproducts' ));

							$aquos_id = $order_item_data['_aquos_id'];
							$aquos_id_array = explode('+', $aquos_id);
							$ignoredproducts = get_option( 'tmsm_aquos_spa_booking_ignoredproducts' );
							$ignoredproducts_array = explode(',', $ignoredproducts);

							if(count($ignoredproducts_array) > 0){
								$aquos_id_array = array_diff($aquos_id_array, $ignoredproducts_array);
								$aquos_id = implode('+',  $aquos_id_array );
							}

							$replacements = [
								$appointment_date,
								sanitize_text_field(trim(str_replace(',', '+', $aquos_id))),
								( is_multisite() ? get_current_blog_id() : 0 ),
								sanitize_text_field($order_item_data['_appointment_time']),
								urlencode($order->get_meta('_billing_title') == '1') ? 'M.' : 'Mme',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_first_name()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_last_name()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_email()))) ?? '',
								$order_item_data['_has_voucher'] ?? '0',
								str_replace( '-', '', $order->get_meta( '_billing_birthdate' ) ) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_address_1(). ' '.$order->get_billing_address_2()))),
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_postcode()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_city()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_phone()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field(trim($order->get_customer_note()))) ?? '',
								self::sanitize_for_webservice(sanitize_text_field( get_bloginfo( 'name' ) )) ?? '',
							];

							// Replace keywords in url
							$settings_webserviceurl = preg_replace( $patterns, $replacements, $settings_webserviceurl );
							if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
								error_log( 'url after:' . $settings_webserviceurl );
							}



							// Connect with cURL
							$ch = curl_init();
							curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
							curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
							curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
							$result = curl_exec( $ch );
							$errors = [];
							$result_array = [];

							$logger = wc_get_logger();

							$logger->info(
								'WebService Request: '. $settings_webserviceurl,
								array(
									'source' => 'tmsm-aquos-spa-booking',
								)
							);

							if(empty($result)){
								if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
									error_log('Web service is not available');
								}
								$errors[] = __( 'Web service is not available', 'tmsm-aquos-spa-booking' );
							}
							else{
								$result_array = json_decode( $result, true );

								$logger->info(
									wc_print_r( $result_array, true ),
									array(
										'source' => 'tmsm-aquos-spa-booking',
									)
								);

								// Debug response
								if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
									error_log('Webservice response');
									error_log( var_export( $result_array, true ) );
									error_log( print_r( curl_getinfo($ch), true ) );
								}

								// No errors, success
								if(!empty($result_array['Status']) && $result_array['Status'] == 'true'){
									wc_update_order_item_meta($order_item_id, '_appointment_processed', 'yes');
									if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
										error_log('Web service submission successful');
									}

								}

								// Some error detected
								else{
									if(!empty($result_array['ErrorCode']) && !empty($result_array['ErrorMessage'])){
										if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
											error_log(sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $result_array['ErrorCode'], $result_array['ErrorMessage']));
										}
										$errors[] = sprintf(__( 'Error code %s: %s', 'tmsm-aquos-spa-booking' ), $result_array['ErrorCode'], $result_array['ErrorMessage']);
									}
									else{
										if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
											error_log('Unknown error');
										}
										$errors[] = __( 'Unknown error', 'tmsm-aquos-spa-booking' );
										$errors[] = print_r($result_array, true);
									}
								}
							}
							curl_close( $ch );
							sleep(2);


							// Notify admin if errors
							if(!empty($errors)) {

								wc_update_order_item_meta($order_item_id, '_appointment_processed', 'no');
								update_post_meta($order_id, '_appointment_error', 'yes');

								$blogname = esc_html( get_bloginfo( 'name' ) );
								$email    = stripslashes( get_option( 'admin_email' ) );
								$subject  = sprintf(__( '%s: TMSM Aquos Spa Booking submission (error %s) for order #%s', 'tmsm-aquos-spa-booking' ), $blogname, $result_array['ErrorCode'] ?? __( 'Unknown error', 'tmsm-aquos-spa-booking' ), $order_id);

								$message  = sprintf(__( 'An error occured while submitting appointment data to the Aquos web service on %s', 'tmsm-aquos-spa-booking' ), $blogname);

								$message .= "\r\n\r\n";
								$message  .= __( 'Customer:', 'tmsm-aquos-spa-booking' );
								$message .= str_replace('<br/>', "\r\n", $order->get_formatted_billing_address());
								$message .= "\r\n";
								$message .= $order->get_billing_email();
								$message .= "\r\n\r\n";
								$message .= sprintf(__( 'Order: %s', 'tmsm-aquos-spa-booking' ), $order->get_edit_order_url());
								$message .= "\r\n";
								$message .= sprintf(__( 'Appointment: %s on %s', 'tmsm-aquos-spa-booking' ), $order_item_data['name'], $order_item_data['_appointment']);
								$message .= "\r\n\r\n";

								$message .= sprintf(__( 'Errors: %s', 'tmsm-aquos-spa-booking' ), implode(', ', $errors));
								$message .= "\r\n";
								$message .= sprintf(__( 'Web Service Request: %s', 'tmsm-aquos-spa-booking' ), str_replace('https://', '', $settings_webserviceurl ) );
								$message .= "\r\n";
								$message .= sprintf(__( 'Aquos Product ID: %s', 'tmsm-aquos-spa-booking' ), $order_item_data['_aquos_id']);

								$headers = 'Auto-Submitted: auto-generated; Content-Type: text/html; charset=UTF-8;';
								$email_sent = wp_mail( $email, $subject, $message, $headers );
								if (  TMSM_AQUOS_SPA_BOOKING_DEBUG && !$email_sent) {
									error_log('Error email sent');
								}

							}
							// Success, send confirmation email to customer
							else{
								if( defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG ){
									error_log('Triggering action woocommerce_order_action_send_appointment_confirmation');
								}

								global $wp_actions;
								do_action( 'woocommerce_order_action_send_appointment_confirmation', $order->get_id() );
							}

						}

					}
				}

			}
		}

		return false;
	}

	/**
	 * Custom sanitize for web service data insertion
	 */
	static function sanitize_for_webservice($string) {

		//return str_replace(' ', '+', $string); // needed when not using urlencode, if not webdeb app doesn't not recognize the app location
		$string = str_replace( '/', '', $string ); // needed by webdev, if not triggers forbidden
		$string = str_replace( '(', '', $string ); // needed by webdev, if not triggers forbidden
		$string = str_replace( ')', '', $string ); // needed by webdev, if not triggers forbidden
		$string = str_replace( '+', '', $string ); // needed by webdev, if not triggers forbidden
		$string = str_replace( ',', '', $string ); // needed by webdev, if not triggers forbidden
		$string = str_replace( '..', '.', $string ); // needed by webdev, if not triggers forbidden when there are 3 consecutives dots in comments
		$string = urlencode( $string );

		return $string;

	}

	/**
	 * Complete
	 */
	protected function complete() {
		parent::complete();
	}

}
$GLOBALS['tmsm_asb_bp'] = new Tmsm_Aquos_Spa_Booking_Background_Process();
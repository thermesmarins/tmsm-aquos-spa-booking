<?php
defined('ABSPATH') or	die('Cheatin&#8217; uh?');

require('vendors/wp-async-request.php');
require('vendors/wp-background-process.php');
if (! defined('TMSM_AQUOS_SPA_BOOKING_TEMPLATES')) {
	define('TMSM_AQUOS_SPA_BOOKING_TEMPLATES', plugin_dir_path(__FILE__));
}
/**
 * Extends the background process class
 *
 * @since 2.7
 *
 * @see WP_Background_Process
 */
class Tmsm_Aquos_Spa_Booking_Background_Process extends Tmsm_WP_Background_Process
{

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
	public function __construct()
	{


		// TODO rendre dynamique le préfix de BDD
		$this->prefix = 'aq_' . get_current_blog_id();

		add_filter('woocommerce_email_classes', array($this, 'register_email'), 90, 1);

		add_action('woocommerce_order_action_send_appointment_confirmation', array($this, 'trigger_confirmation'));
		add_action('woocommerce_order_action_send_appointment_message', array($this, 'trigger_message'));
		add_action('woocommerce_order_action_send_appointment_error', array($this, 'trigger_error'));
		parent::__construct();
	}


	public function trigger_confirmation($order_id)
	{

		$email_classes  = WC()->mailer()->emails;

		$email_appointment = $email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment'];
		$email_appointment->trigger($order_id);
	}

	public function trigger_message($order_id)
	{

		$email_classes  = WC()->mailer()->emails;

		$email_appointment_with_message = $email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message'];
		$email_appointment_with_message->trigger($order_id);
	}

	public function trigger_error($order_id)
	{
		$email_classes  = WC()->mailer()->emails;
		$email_appointment_error = $email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Error'];
		$email_appointment_error->trigger($order_id, $order);
	}

	/**
	 * @param array $email_classes
	 *
	 * @return array
	 */
	public function register_email($email_classes)
	{

		$email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment'] = require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-email-appointment.php');

		$email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message'] = require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-email-appointment-message.php');
		// Enregistrement de l'email d'annulation pour le client
		$email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled'] = require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-email-appointment-cancelled.php');
		$email_classes['Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Error'] = require_once(plugin_dir_path(dirname(__FILE__)) . 'includes/class-tmsm-aquos-spa-booking-email-appointment-error.php');


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
	protected function task($item)
	{
		global $wp_actions;

		$order_id = $item['order_id'];

		if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
			error_log('Tmsm_Aquos_Spa_Booking_Background_Process task with order id: ' . $order_id);
			error_log(print_r($item, true));
		}

		$order = wc_get_order($order_id);
		if (! empty($order)) {
			if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
				error_log('Tmsm_Aquos_Spa_Booking_Background_Process is order');
			}

			foreach ($order->get_items() as $order_item_id => $order_item_data) {

				if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
					error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item_data:');
					error_log(print_r($order_item_data, true));
				}

				// Only items with an appointment
				if (!empty($order_item_data['_appointment'])) {
					// Ajout pour faire attendre l'email de confirmation automatique de woocommerce
					update_post_meta($order_id, '_reservation_status', 'pending');
					if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
						error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is appointment');
					}

					// If not aready processed, processing
					if (wc_get_order_item_meta($order_item_id, '_appointment_processed', true) !== 'yes') {

						if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
							error_log('Tmsm_Aquos_Spa_Booking_Background_Process order_item is not processed');
						}
						// Add to aquos with or without aquatonic options in note !
						if (wc_get_order_item_meta($order_item_id, 'pa_parcoursaquatonic', true) === "sans-parcours-aquatonic") {
							$pa = '';
						} else {
							$pa = '-AVEC PA- ';
						}
						if (wc_get_order_item_meta($order_item_id, '_voucher_number', true) !== "") {
							$voucher_number = wc_get_order_item_meta($order_item_id, '_voucher_number', true);
							$voucher_number_display = " numero de bon cadeau : " . $voucher_number . " ";
						} else {
							$voucher_number = '';
							$voucher_number_display = '';
						}
						error_log('voucher_number: ' . print_r($voucher_number, true) . " ");
						$note = $pa . $voucher_number_display;
						// Prepare web service params
						$settings_webserviceurl = get_option('tmsm_aquos_spa_booking_webserviceurlsubmit');
						$settings_aquossiteid = get_option('tmsm_aquos_spa_booking_aquossiteid');
						if (! empty($settings_webserviceurl) && isset($settings_aquossiteid)) {

							$appointment_date = sanitize_text_field(str_replace('-', '', trim($order_item_data['_appointment_date'])));
							$appointment_time = sanitize_text_field($order_item_data['_appointment_time']);

							$ignoredproducts_array = explode(',', get_option('tmsm_aquos_spa_booking_ignoredproducts'));

							$aquos_id = $order_item_data['_aquos_id'];
							$aquos_id_array = explode('+', $aquos_id);
							$ignoredproducts = get_option('tmsm_aquos_spa_booking_ignoredproducts');
							$ignoredproducts_array = explode(',', $ignoredproducts);

							if (count($ignoredproducts_array) > 0) {
								$aquos_id_array = array_diff($aquos_id_array, $ignoredproducts_array);
							}
							$aquos_id_array_formatted = [];
							foreach ($aquos_id_array as $aquos_id) {
								$aquos_id_array_formatted[] = ['id_product' => $aquos_id];
							}
							$data = [
								'id_site'       => $settings_aquossiteid,
								'date'          => $appointment_date,
								'hour'          => sanitize_text_field($order_item_data['_appointment_time']),
								'civility'      => urlencode($order->get_meta('_billing_title') == '1') ? 'M.' : 'Mme',
								'lastname'      => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_last_name()))) ??
									'',
								'firstname'     => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_first_name()))) ??
									'',
								'email'         => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_email()))) ?? '',
								'gift_voucher'  => $order_item_data['_has_voucher'] ?? '0',
								'birth_date'    => str_replace(['-', '/'], '', $order->get_meta('_billing_birthdate')) ?? '',
								'address'       => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_address_1() . ' '
									. $order->get_billing_address_2()))),
								'postal_code'   => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_postcode()))) ??
									'',
								'city'          => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_city()))) ?? '',
								'phone_number'  => self::sanitize_for_webservice(sanitize_text_field(trim($order->get_billing_phone()))) ?? '',
								'note'          => $note . self::sanitize_for_webservice(sanitize_text_field(trim($order->get_customer_note()))) ?? '',
								'list_products' => $aquos_id_array_formatted,
							];

							$body = json_encode($data);

							$headers = [
								'Content-Type' => 'application/json; charset=utf-8',
								'X-Signature' => $this->aquos_generate_signature($body),
								'Cache-Control' => 'no-cache',
							];

							$response = wp_remote_post(
								$settings_webserviceurl,
								array(
									'headers'     => $headers,
									'body'        => $body,
									'data_format' => 'body',
									'timeout' => 10,
								)
							);
							$response_code = wp_remote_retrieve_response_code($response);
							$response_data = json_decode(wp_remote_retrieve_body($response));

							$errors = [];

							$logger = wc_get_logger();

							if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
								$logger->info(
									'TMSM Aquos Spa Booking Request: ' . $settings_webserviceurl . ' ' . wc_print_r($data, true),
									array(
										'source' => 'tmsm-aquos-spa-booking',
									)
								);
							}

							if (empty($response)) {
								error_log(__('Web service is not available', 'tmsm-aquos-spa-booking'));
								$errors[] = __('Web service is not available', 'tmsm-aquos-spa-booking');
							} else {

								$logger->info(
									wc_print_r($response_data, true),
									array(
										'source' => 'tmsm-aquos-spa-booking',
									)
								);

								if ($response_code >= 400) {
									error_log(sprintf(__('Error: Delivery URL returned response code: %s', 'tmsm-aquos-spa-booking'), absint($response_code)));
									$errors[] = sprintf(__('Error: Delivery URL returned response code: %s', 'tmsm-aquos-spa-booking'), absint($response_code));
								}

								if (is_wp_error($response)) {
									error_log('Error message: ' . $response->get_error_message());
									$errors[] = sprintf(__('Error message: %s', 'tmsm-aquatonic-course-booking'), $response->get_error_message());
								}

								error_log('response from aquos' . print_r($response_data->appointment_id, true));
								// No errors, success
								if (! empty($response_data->Status) && $response_data->Status == 'true') {
									wc_update_order_item_meta($order_item_id, '_appointment_processed', 'yes');
									// todo gerer le retour des id's multiples
									if (!empty($response_data->appointment_id)) {
										// Faire un tableau si plusieurs id's avec la variable appointment_ids
										$appointment_ids = array();
										// Faire une boucle sur chaque id de la réponse
										if (is_array($response_data->appointment_id)) {
											foreach ($response_data->appointment_id as $appointment_id) {
												$appointment_ids[] = $appointment_id->id;
											}
										}
										// compter le nombre d'ids si supérieur à 1, on enregistre un tableau en forme de chaîne
										if (count($appointment_ids) > 1) {
											$appointment_ids = implode(',', $appointment_ids);
										} else {
											$appointment_ids = $appointment_ids[0];
										}
										error_log('appointment_ids: ' . print_r($appointment_ids, true));
										// else {
										// 	$appointment_ids[] = $response_data->appointment_id;
										// }
										// 

										wc_update_order_item_meta($order_item_id, '_aquos_appointment_id', $appointment_ids);
										$order->add_meta_data('_aquos_appointment_id', $appointment_ids);
										// wc_update_order_item_meta($order_item_id, '_aquos_appointment_id', $response_data->appointment_id);
										// $order->add_meta_data('_aquos_appointment_id', $response_data->appointment_id);
										$order->save();
									}
									if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
										error_log('Web service submission successful');
									}
								}
								// Some error detected
								else {
									if (! empty($response_data->ErrorCode) && ! empty($response_data->ErrorMessage)) {
										error_log(sprintf(__('Error code %s: %s', 'tmsm-aquos-spa-booking'), $response_data->ErrorCode, $response_data->ErrorMessage));
										$errors[] = sprintf(__('Error code %s: %s', 'tmsm-aquos-spa-booking'), $response_data->ErrorCode, $response_data->ErrorMessage);
									} else {
										error_log(__('Unknown error', 'tmsm-aquos-spa-booking'));
										$errors[] = __('Unknown error', 'tmsm-aquos-spa-booking');
									}
								}
							}
							sleep(2);

							// Notify admin if errors
							if (!empty($errors)) {

								$logger->error(
									wc_print_r($errors, true),
									array(
										'source' => 'tmsm-aquos-spa-booking',
									)
								);

								$note_error = null;
								wc_update_order_item_meta($order_item_id, '_appointment_processed', 'no');
								wc_update_order_item_meta($order_item_id, '_appointment_error', 'yes');
								update_post_meta($order_id, '_appointment_error', 'yes');

								if (empty($order_item_data['name']) || empty($order_item_data['_appointment']) || empty(implode(', ', $errors))) {
									$note_error .= '...' . $order_item_data['name'] . ' ' . $order_item_data['_appointment'] . ' ' . print_r($errors, true);
								} else {
									$note_error .= sprintf(__('Appointment: %s on %s has not inserted because of error: %s', 'tmsm-aquos-spa-booking'), $order_item_data['name'], $order_item_data['_appointment'], implode(', ', $errors));
								}

								/*
								// Send an email to admin when error
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
								}*/

								error_log('Error: ' . print_r($errors, true));
								update_post_meta($order_id, '_reservation_status', 'failed');
								update_post_meta($order_id, '_appointment_error', 'yes');
								$order->add_order_note($note_error, false, false);
								do_action('woocommerce_order_action_send_appointment_error', $order_id, $order);
								continue;
							} else {
								error_log('Confirmed');
								update_post_meta($order_id, '_reservation_status', 'confirmed');
								do_action('woocommerce_order_action_send_appointment_confirmation', $order->get_id());
								do_action('woocommerce_order_action_send_appointment_message', $order->get_id());
								// On déclenche manuellement l'e-mail de confirmation standard
								// $emails = WC()->mailer()->get_emails();
								// if (isset($emails['WC_Email_Customer_Processing_Order'])) {
								// 	$emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
								// }
								
							}
						}
					}
				}
			} // end $order->get_items()


			sleep(5);

			if (defined('TMSM_AQUOS_SPA_BOOKING_DEBUG') && TMSM_AQUOS_SPA_BOOKING_DEBUG) {
				error_log('Triggering action woocommerce_order_action_send_appointment_confirmation');
				error_log('Triggering action woocommerce_order_action_send_appointment_message');
			}
			// do_action('woocommerce_order_action_send_appointment_confirmation', $order->get_id());
			// do_action('woocommerce_order_action_send_appointment_message', $order->get_id());
		}

		return false;
	}

	/**
	 * Aquos: generate signature
	 *
	 * @param string $payload
	 *
	 * @return string
	 */
	private function aquos_generate_signature($payload)
	{
		$hash_algo = 'sha256';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode(hash_hmac($hash_algo, $payload, wp_specialchars_decode($this->aquos_secret(), ENT_QUOTES), true));
	}

	/**
	 * Aquos: returns secret
	 *
	 * @return string
	 */
	private function aquos_secret()
	{

		$secret = get_option('tmsm_aquos_spa_booking_aquossecret');

		return $secret;
	}

	/**
	 * Custom sanitize for web service data insertion
	 */
	static function sanitize_for_webservice($string)
	{

		//return str_replace(' ', '+', $string); // needed when not using urlencode, if not webdeb app doesn't not recognize the app location
		$string = str_replace('/', '', $string); // needed by webdev, if not triggers forbidden
		$string = str_replace('(', '', $string); // needed by webdev, if not triggers forbidden
		$string = str_replace(')', '', $string); // needed by webdev, if not triggers forbidden
		$string = str_replace('+', '', $string); // needed by webdev, if not triggers forbidden
		$string = str_replace(',', '', $string); // needed by webdev, if not triggers forbidden
		$string = str_replace('..', '.', $string); // needed by webdev, if not triggers forbidden when there are 3 consecutives dots in comments
		$string = urlencode($string);

		return $string;
	}

	/**
	 * Complete
	 */
	protected function complete()
	{
		parent::complete();
	}
}
$GLOBALS['tmsm_asb_bp'] = new Tmsm_Aquos_Spa_Booking_Background_Process();

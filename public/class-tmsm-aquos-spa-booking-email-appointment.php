<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_KIA_Class_Email', false ) ) :

	/**
	 * A custom Order WooCommerce Email class
	 *
	 * @since 0.1
	 * @extends \WC_Email
	 */
	class Tmsm_Aquos_Spa_Booking_Class_Email_Appointment extends WC_Email {


		/**
		 * Set email defaults
		 *
		 * @since 0.1
		 */
		public function __construct() {

			error_log('construct Tmsm_Aquos_Spa_Booking_Class_Email_Appointment');

			// set ID, this simply needs to be a unique name
			$this->id = 'tmsm_aquos_spa_booking_appointment';
			$this->customer_email = true;

			// this is the title in WooCommerce Email settings
			$this->title = __( 'Order Email for Appointment', 'tmsm-aquos-spa-booking' );

			// this is the description in WooCommerce email settings
			$this->description = __( 'Notification emails are sent when a customer books an appointment', 'tmsm-aquos-spa-booking' );

			// these are the default heading and subject lines that can be overridden using the settings
			$this->heading = __( 'Appointment Confirmation', 'tmsm-aquos-spa-booking' );
			$this->subject = __( 'Appointment Confirmation', 'tmsm-aquos-spa-booking' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
			$this->template_html  = 'emails/tmsm-aquos-spa-booking-appointment.php';
			$this->template_plain = 'emails/plain/tmsm-aquos-spa-booking-appointment.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Manually send via admin order actions.
			add_action( 'woocommerce_order_action_send_appointment_confirmation', array( $this, 'trigger' ) );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();

		}


		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Appointment Confirmation', 'tmsm-aquos-spa-booking' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Appointment Confirmation', 'tmsm-aquos-spa-booking' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {

			error_log('Triggered action woocommerce_order_action_send_appointment_confirmation');

			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$success = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

				if( $success ) {
					// ADD THE ORDER NOTE
					//$message = sprintf( __( 'Class detail for "%s" is sent.', 'zlp_class' ), $order->get_meta( 'class_name', true ) );

					//$order->add_order_note( $message );

					// ADD THE FLAG SO THIS ACTION WON'T BE SHOWN AGAIN (I don't really now if that's good... you might need to resend it)
					//	$order->update_meta_data( '_wc_order_class_detail_sent', 'yes' );
					//$order->save();
				}
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				)
			);
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( 'Thanks for using {site_address}!', 'tmsm-aquos-spa-booking' );
		}

	} // end \Tmsm_Aquos_Spa_Booking_Class_Email_Appointment class

endif;

return new Tmsm_Aquos_Spa_Booking_Class_Email_Appointment();
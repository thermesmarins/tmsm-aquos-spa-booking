<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

if ( ! class_exists( 'Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message', false ) ) :

	/**
	 * A custom Order WooCommerce Email class
	 *
	 * @since 0.1
	 * @extends \WC_Email
	 */
	class Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message extends WC_Email {


		/**
		 * Set email defaults
		 *
		 * @since 0.1
		 */
		public function __construct() {


			// set ID, this simply needs to be a unique name
			$this->id = 'tmsm_aquos_spa_booking_appointment_message';
			$this->customer_email = false;

			// this is the title in WooCommerce Email settings
			$this->title = __( 'Order Email for Appointment with Message or Error', 'tmsm-aquos-spa-booking' );

			// this is the description in WooCommerce email settings
			$this->description = __( 'Notification emails are sent when a customer books an appointment', 'tmsm-aquos-spa-booking' );

			// these are the default heading and subject lines that can be overridden using the settings
			$this->heading = __( 'Appointment with Message or Error', 'tmsm-aquos-spa-booking' );
			$this->subject = __( 'Appointment #{order_number} with Message or Error', 'tmsm-aquos-spa-booking' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
			$this->template_base  = TMSM_AQUOS_SPA_BOOKING_TEMPLATES. 'templates/';
			$this->template_html  = 'emails/tmsm-aquos-spa-booking-appointment-message.php';
			$this->template_plain = 'emails/plain/tmsm-aquos-spa-booking-appointment-message.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Manually send via admin order actions.
			add_action( 'woocommerce_order_action_send_appointment_message', array( $this, 'trigger' ) );

			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}


		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Appointment #{order_number} with Message or Error', 'tmsm-aquos-spa-booking' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Appointment with Message or Error', 'tmsm-aquos-spa-booking' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {

			// Check if email was sent
			if(get_post_meta($order_id, '_appointment_message_sent', true) !== 'yes') {

				$this->setup_locale();

				if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
					$order = wc_get_order( $order_id );
				}

				if ( is_a( $order, 'WC_Order' ) ) {
					$this->object                         = $order;
					$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
					$this->placeholders['{order_number}'] = $this->object->get_order_number();

					$subject = $this->get_subject();

					if(! empty( $order->get_customer_note())){
						$subject = __( 'Appointment #{order_number} with a Message', 'tmsm-aquos-spa-booking' );

					}
					if( get_post_meta( $order->get_id(), '_appointment_error', true ) ){
						$subject = __( 'Appointment #{order_number} with an Error', 'tmsm-aquos-spa-booking' );
					}
					if(! empty( $order->get_customer_note()) && get_post_meta( $order->get_id(), '_appointment_error', true )){
						$subject = $this->get_subject();
					}
					$subject = apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $subject ), $this->object, $this );

				}

				// Only send if customer notes or if appointment error
				if ( $this->is_enabled() && $this->get_recipient()
				     && ( ! empty( $order->get_customer_note() || get_post_meta( $order->get_id(), '_appointment_error', true ) ) ) ) {

					$success = $this->send( $this->get_recipient(), $subject, $this->get_content(), $this->get_headers(),
						$this->get_attachments() );

					if ( $success ) {
						update_post_meta( $order_id, '_appointment_message_sent', 'yes' );
					}
				}

				$this->restore_locale();
			}

		}

		/**
		 * Get email content.
		 *
		 * @return string
		 */
		public function get_content() {
			$this->sending = true;

			if ( 'plain' === $this->get_email_type() ) {
				$email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $this->get_content_plain() ) ), 70 );
			} else {
				$email_content = $this->get_content_html();
			}

			return $email_content;
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
					'sent_to_admin'      => true,
					'plain_text'         => false,
					'email'              => $this,
					'notes'              => $this->get_private_order_notes($this->object),
				),
				'',
				$this->template_base
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
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get Private notes
		 *
		 * @param $object
		 *
		 * @return string
		 */
		public function get_private_order_notes( $object){

			$notes = array();
			$notes_html = null;


			if ( is_a( $object, 'WC_Order' ) ) {
				$order = $object;
				$notes = wc_get_order_notes( [
						'order_id' => $order->get_id(),
						'order'    => 'ASC',
						'type'     => 'internal',
					]
				);

			}

			// Remove first order status change
			array_shift($notes );

			foreach($notes as $note){
				$notes_html .= wpautop( wptexturize( wp_kses_post( $note->content ) ) ) .'<br>';
			}

			return $notes_html;
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @return string
		 */
		public function get_default_additional_content() {
			return '';
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {

			parent::init_form_fields();

			$this->form_fields['recipient'] = array(
				'title'       => __( 'Recipient(s)', 'tmsm-aquos-spa-booking' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'tmsm-aquos-spa-booking' ),
					'<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			);
		}

	} // end \Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message class

endif;
return new Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Message();
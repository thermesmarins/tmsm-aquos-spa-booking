<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

if ( ! class_exists( 'Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled', false ) ) :

    /**
     * A custom Order WooCommerce Email class for cancelled appointments
     *
     * @since 0.1
     * @extends \WC_Email
     */
    class Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled extends WC_Email {


        /**
         * Set email defaults
         *
         * @since 0.1
         */
        public function __construct() {

            // set ID, this simply needs to be a unique name
            $this->id = 'tmsm_aquos_spa_booking_appointment_cancelled';
            $this->customer_email = true;

            // this is the title in WooCommerce Email settings
            $this->title = __( 'Order Email for Cancelled Appointment', 'tmsm-aquos-spa-booking' );

            // this is the description in WooCommerce email settings
            $this->description = __( 'Notification emails are sent to the customer when their appointment is cancelled', 'tmsm-aquos-spa-booking' );

            // these are the default heading and subject lines that can be overridden using the settings
            $this->heading = __( 'Appointment Cancellation Confirmation', 'tmsm-aquos-spa-booking' );
            $this->subject = __( 'Your Appointment has been Cancelled', 'tmsm-aquos-spa-booking' );

            // these define the locations of the templates that this email should use
            $this->template_base   = TMSM_AQUOS_SPA_BOOKING_TEMPLATES. 'templates/';
            $this->template_html   = 'emails/tmsm-aquos-spa-booking-appointment-cancelled.php';
            $this->template_plain  = 'emails/plain/tmsm-aquos-spa-booking-appointment-cancelled.php';
            $this->placeholders    = array(
                '{order_date}'   => '',
                '{order_number}' => '',
            );

            // Manually send via admin order actions (optional, you might trigger this via the order status change)
            // add_action( 'woocommerce_order_action_send_appointment_cancellation', array( $this, 'trigger' ) );

            // Call parent constructor to load any other defaults not explicitly defined here
            parent::__construct();

            // Trigger this email when the order status changes to cancelled
            add_action( 'woocommerce_order_status_cancelled', array( $this, 'trigger' ), 10, 1 );
             // Hook into the settings fields
             add_action( 'woocommerce_email_settings_fields', array( $this, 'init_form_fields' ), 10 );
             
        }


        /**
         * Get email subject.
         *
         * @since   3.1.0
         * @return string
         */
        public function get_default_subject() {
            return __( 'Your Appointment has been Cancelled', 'tmsm-aquos-spa-booking' );
        }

        /**
         * Get email heading.
         *
         * @since   3.1.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'Appointment Cancellation Confirmation', 'tmsm-aquos-spa-booking' );
        }

        /**
         * Trigger the sending of this email.
         *
         * @param int             $order_id The order ID.
         * @param WC_Order|false $order    Order object.
         */
        public function trigger( $order_id, $order = false ) {

            $this->setup_locale();

            if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
                $order = wc_get_order( $order_id );
            }

            if ( is_a( $order, 'WC_Order' ) ) {
                $this->object                           = $order;
                $this->recipient                      = $this->object->get_billing_email();
                $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
                $this->placeholders['{order_number}'] = $this->object->get_order_number();
            }

            if ( $this->is_enabled() && $this->get_recipient() ) {
                $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            }

            $this->restore_locale();
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
                    'sent_to_admin'      => false,
                    'plain_text'         => false,
                    'email'              => $this,
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
         * Default content to show below main email content.
         *
         * @return string
         */
        public function get_default_additional_content() {
            return '';
        }        /**
        * Initialize settings form fields.
        *
        * @since 2.0.0
        */
       public function init_form_fields() {
           $this->form_fields = array(
               'enabled' => array(
                   'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
                   'type' 			=> 'checkbox',
                   'description' 	=> sprintf( __( 'Enable this email notification. %s', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email' ) . '">' . __( 'Manage email options', 'woocommerce' ) . '</a>' ),
                   'default' 		=> 'yes',
               ),
               'subject' => array(
                   'title' 		=> __( 'Subject', 'woocommerce' ),
                   'type' 			=> 'text',
                   'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->get_default_subject() ),
                   'placeholder' 	=> $this->get_default_subject(),
                   'default' 		=> '',
                   'css' 			=> 'width:300px;',
               ),
               'heading' => array(
                   'title' 		=> __( 'Email heading', 'woocommerce' ),
                   'type' 			=> 'text',
                   'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->get_default_heading() ),
                   'placeholder' 	=> $this->get_default_heading(),
                   'default' 		=> '',
                   'css' 			=> 'width:300px;',
               ),
               
               'additional_content' => array(
                   'title' 		=> __( 'Additional content', 'woocommerce' ),
                   'description' 	=> __( 'Text to appear below the main email content.', 'woocommerce' ),
                   'css' 			=> 'width:400px; height: 80px;',
                   'placeholder' 	=> __( 'N/A', 'woocommerce' ),
                   'type' 			=> 'textarea',
                   'default' 		=> $this->get_default_additional_content(),
                   'desc_tip' 		=> true,
               ),
                'email_type' => array(
                     'title' 		=> __( 'Email type', 'woocommerce' ),
                     'type' 			=> 'select',
                     'description' 	=> __( 'Choose which format of email to send.', 'woocommerce' ),
                     'default' 		=> 'html',
                     'class' 			=> 'email_type',
                     'options' 		=> array(
                          'plain' 	=> __( 'Plain text', 'woocommerce' ),
                          'html' 	=> __( 'HTML', 'woocommerce' ),
                          'multipart' => __( 'Multipart', 'woocommerce' ),
                     ),
                ),
           );
       }

    } // end \Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled class

endif;

return new Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled();
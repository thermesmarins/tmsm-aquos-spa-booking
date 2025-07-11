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

private $additional_data = array();
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

                 // Récupère le modèle de texte depuis les réglages de ton plugin custom
                $cancellation_template = get_option('tmsm_aquos_spa_booking_cancellation_text_template');
                $current_user = wp_get_current_user();
                $user_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;

                $appointment_date_str = '';
                $appointment_time = '';
                $service_name = '';

                foreach ( $order->get_items() as $item ) {
                    $appointment_date_str = $item->get_meta( '_appointment_date' );
                    $appointment_time = $item->get_meta( '_appointment_time' );
                    $service_name = $item->get_name();
                    break; // On ne prend que le premier service
                    }
                    $date = new DateTime( $appointment_date_str );
                    $appointment_date = $appointment_date_str ? date_i18n( get_option( 'date_format' ), strtotime( $appointment_date_str ) ) : '';
                    if ( empty( $cancellation_template ) ) {
                        error_log( 'Cancellation template is empty.' ); // Debugging line
                        return;
                    }

                    // Crée un tableau d'arguments pour le remplacement
                    $args = array(
                        '[user_name]'       => esc_html( $user_name ),
                        '[appointment_date]' => esc_html( $date->format( 'd-m-Y' ) ),
                        '[appointment_time]' => esc_html( $appointment_time ),
                        '[service_name]'    => esc_html( $service_name ),
                        '[new_appointment_link]' => esc_url(home_url().'/prendre-rdv/' ),
                    );
                    // Remplace les placeholders dans le modèle de texte par les valeurs dynamiques
        $dynamic_cancellation_text = str_replace( array_keys( $args ), array_values( $args ), $cancellation_template );
        // Passe le contenu dynamique au template
        $this->additional_data['dynamic_cancellation_text'] = wp_kses_post( wpautop( $dynamic_cancellation_text ) );
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
                    'order'                    => $this->object,
                    'email_heading'            => $this->get_heading(),
                    'additional_content'       => $this->get_additional_content(),
                    'dynamic_cancellation_text'=> $this->additional_data['dynamic_cancellation_text'] ?? '',
                    'sent_to_admin'            => false,
                    'plain_text'               => false,
                    'email'                    => $this,
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
                   'title' 		=> __( 'Enable/Disable', 'tmsm-aquos-spa-booking' ),
                   'type' 			=> 'checkbox',
                   'description' 	=> sprintf( __( 'Enable this email notification. %s', 'tmsm-aquos-spa-booking' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email' ) . '">' . __( 'Manage email options', 'tmsm-aquos-spa-booking' ) . '</a>' ),
                   'default' 		=> 'yes',
               ),
               'subject' => array(
                   'title' 		=> __( 'Subject', 'tmsm-aquos-spa-booking' ),
                   'type' 			=> 'text',
                   'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'tmsm-aquos-spa-booking' ), $this->get_default_subject() ),
                   'placeholder' 	=> $this->get_default_subject(),
                   'default' 		=> '',
                   'css' 			=> 'width:300px;',
               ),
               'heading' => array(
                   'title' 		=> __( 'Email heading', 'tmsm-aquos-spa-booking' ),
                   'type' 			=> 'text',
                   'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'tmsm-aquos-spa-booking' ), $this->get_default_heading() ),
                   'placeholder' 	=> $this->get_default_heading(),
                   'default' 		=> '',
                   'css' 			=> 'width:300px;',
               ),
               
               'additional_content' => array(
                   'title' 		=> __( 'Additional content', 'tmsm-aquos-spa-booking' ),
                   'description' 	=> __( 'Text to appear below the main email content.', 'tmsm-aquos-spa-booking' ),
                   'css' 			=> 'width:400px; height: 80px;',
                   'placeholder' 	=> __( 'N/A', 'tmsm-aquos-spa-booking' ),
                   'type' 			=> 'textarea',
                   'default' 		=> $this->get_default_additional_content(),
                   'desc_tip' 		=> true,
               ),
                'email_type' => array(
                     'title' 		=> __( 'Email type', 'tmsm-aquos-spa-booking' ),
                     'type' 			=> 'select',
                     'description' 	=> __( 'Choose which format of email to send.', 'tmsm-aquos-spa-booking' ),
                     'default' 		=> 'html',
                     'class' 			=> 'email_type',
                     'options' 		=> array(
                          'plain' 	=> __( 'Plain text', 'tmsm-aquos-spa-booking' ),
                          'html' 	=> __( 'HTML', 'tmsm-aquos-spa-booking' ),
                          'multipart' => __( 'Multipart', 'tmsm-aquos-spa-booking' ),
                     ),
                ),
           );
       }

    } // end \Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled class

endif;

return new Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Cancelled();
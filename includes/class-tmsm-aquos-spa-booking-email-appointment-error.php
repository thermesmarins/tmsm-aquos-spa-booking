<?php
if (! defined('ABSPATH')) exit; // Exit if accessed directly


if (! class_exists('WC_Email')) {
    return;
}

if (! class_exists('Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Error')) :

    class Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Error extends WC_Email
    {
        private $additional_data = array();
        /**
         * 1. Le Constructeur
         */
        public function __construct()
        {
            // L'identifiant unique de ton email
            $this->id             = 'spa_appointment_error';
            $this->title          = 'Spa : Erreur de synchronisation';
            $this->description    = 'Email envoyé au client quand le rendez-vous n\'a pas pu être ajouté à la base de données du Spa.';

            // --- CONFIGURATION STRICTE POUR ÉVITER L'ERREUR ---
            $this->customer_email = true;

            // Chemins des templates
            $this->template_base  = TMSM_AQUOS_SPA_BOOKING_TEMPLATES . 'templates/';
            $this->template_html  = 'emails/tmsm-aquos-spa-booking-appointment-error.php';
            $this->template_plain = 'emails/plain/tmsm-aquos-spa-booking-appointment-error.php';
            $this->placeholders   = array(
                '{order_date}'   => '',
                '{order_number}' => '',
            );
            // --------------------------------------------------

            $this->heading        = 'Votre rendez-vous n\'a pas pu être ajouté';
            $this->subject        = 'Erreur lors de votre réservation';
            // On appelle le constructeur parent impérativement
            parent::__construct();

            // Cible par défaut
            $this->recipient = '';
            
        }

        /**
         * 2. Le Déclencheur (Trigger)
         */
        public function trigger($order_id, $order = false)
        {
            if (get_post_meta($order_id, '_appointment_error_message_sent', true) !== 'yes') {

                $this->setup_locale();
                $note = $note ?? 'Ceci est un message de test';
                if ($order_id && ! is_a($order, 'WC_Order')) {
                    $order = wc_get_order($order_id);
                }

                if (is_a($order, 'WC_Order')) {
                    $this->object                           = $order;
                    $this->recipient                      = $this->object->get_billing_email();
                    $this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
                    $this->placeholders['{order_number}'] = $this->object->get_order_number();
                }

                $phone_number = '';
                $site_name = get_bloginfo( 'id' );
                switch($site_name) {
                    case 'Aquatonic Rennes':
                        $phone_number = '02 99 237 877';
                        break;
                    case 'Aquatonic Nantes':
                        $phone_number = '02 40 41 89 89';
                        break;
                    case 'Aquatonic Paris':
                        $phone_number = '01 60 31 01 01';
                        break;
                }

                $error_email_template = get_option('tmsm_aquos_spa_booking_error_email_template');
                $current_user = wp_get_current_user();
                $user_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
                $appointment_date_str = '';
                $appointment_time = '';
                $appointment_name = '';

                foreach ( $order->get_items() as $item ) {
                    $appointment_date_str = $item->get_meta( '_appointment_date' );
                    $appointment_time = $item->get_meta( '_appointment_time' );
                    $appointment_name = $item->get_name();
                    break; // On ne prend que le premier service
                    }
                    $date = new DateTime( $appointment_date_str );
                    if ( $appointment_date_str ) {
                        $timestamp = strtotime( $appointment_date_str );
                        
                        // Récupère uniquement le jour (ex: "mardi")
                        $appointment_day = date_i18n( 'l', $timestamp ); 
                        
                        // Récupère la date selon le format réglé dans WordPress (ex: "24 février 2026")
                        $appointment_date = date_i18n( get_option( 'date_format' ), $timestamp );
                    } else {
                        $appointment_day  = '';
                        $appointment_date = '';
                    }
                    if ( empty( $error_email_template ) ) {
                        error_log( 'Error email template is empty.' ); // Debugging line
                        return;
                    }
                $args = array(
                    '[user_name]'       => esc_html( $user_name ),
                    '[site_url]'        => esc_url(home_url().'/prendre-rdv/' ),
                    '[phone_number]'    => esc_html( $phone_number ),
                    '[appointment_date]' => esc_html( ucfirst($appointment_day) . ' ' . $appointment_date ),
                    '[appointment_time]' => esc_html( $appointment_time ),
                    '[appointment_name]' => esc_html( $appointment_name ),
                );
                $error_email_template = str_replace( array_keys( $args ), array_values( $args ), $error_email_template );
                $this->additional_data['error_email_template'] = wp_kses_post( wpautop( $error_email_template ) );
               
                
                if ($this->is_enabled() && $this->get_recipient()) {
                    $success = $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

                    if( $success ) {
                        update_post_meta($order_id, '_appointment_error_message_sent', 'yes');
                    }
                }
                $this->restore_locale();
            }
        }

        /**
         * 3. Le Contenu HTML
         */
        public function get_content_html()
        {
            return wc_get_template_html(
                $this->template_html,
                array(
                    'order'         => $this->object,
                    'email_heading' => $this->get_heading(),
                    'appointment'   => $this->object,
                    'order_date'               => $this->placeholders['{order_date}'],
                    'order_number'             => $this->placeholders['{order_number}'],
                    'additional_content'       => $this->get_additional_content(),
                    'additional_data' => $this->additional_data['error_email_template'] ?? "",
                    'sent_to_admin' => false,
                    'plain_text'    => false,
                    'email'         => $this
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
                    'additional_data' => $this->additional_data['error_email_template'] ?? "",
                    'sent_to_admin'      => false,
                    'plain_text'         => true,
                    'email'              => $this,
                ),
                '',
                $this->template_base
            );
        }

        /**
         * Contenu de secours (Affiché si le template HTML est manquant)
         */
        public function get_content()
        {
            $this->sending = true;

            if ( 'plain' === $this->get_email_type() ) {
                $email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $this->get_content_plain() ) ), 70 );
            } else {
                $email_content = $this->get_content_html();
            }
            return $email_content;
        }
    }

endif;

return new Tmsm_Aquos_Spa_Booking_Class_Email_Appointment_Error();

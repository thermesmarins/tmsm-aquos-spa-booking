<?php

/**
 * Customer appointment cancelled email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-appointment-cancelled.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php /* translators: %s: Customer first name */ ?>


<?php // Tu peux ajouter ici des informations spécifiques à l'annulation (raison, date, etc.) 
if ( ! empty( $email->additional_data['dynamic_cancellation_text'] ) ) {
    echo $email->additional_data['dynamic_cancellation_text'];
} else {
    // Texte par défaut si le modèle n'est pas configuré
    ?>
    <p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
    <p><?php printf(esc_html__( 'We inform you that your appointment %s has been cancelled.', 'tmsm-aquos-spa-booking' ), esc_html($order->get_order_number())); ?></p>
    <?php
}
?>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @since 3.0.0
 */
// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

// echo '<p>' . esc_html__( 'Voici les détails de votre commande annulée :', 'tmsm-aquos-spa-booking' ) . '</p>';

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
// do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 * Displayed after the customer details table.
 */
if ( $additional_content ) {
	echo '<p>' . wp_kses_post( wpautop( wptexturize( $additional_content ) ) ) . '</p>';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
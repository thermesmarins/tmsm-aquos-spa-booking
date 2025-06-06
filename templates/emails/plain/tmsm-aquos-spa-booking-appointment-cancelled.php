<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer first name */
if ( ! empty( $email->additional_data['dynamic_cancellation_text'] ) ) {
    echo wp_strip_all_tags( $email->additional_data['dynamic_cancellation_text'] );
} else {
    printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) );
    echo "\n\n";
    printf(esc_html__( 'We inform you that your appointment %s has been cancelled.', 'tmsm-aquos-spa-booking' ), esc_html($order->get_order_number()));
    echo "\n\n";
}

// Tu peux ajouter ici des informations spécifiques à l'annulation (raison, date, etc.) 

// echo esc_html__( 'Voici les détails de votre commande annulée :', 'tmsm-aquos-spa-booking' ) . "\n\n";

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @since 3.0.0
 */
// do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------------------------------------\n\n";

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_strip_all_tags( wptexturize( $additional_content ) ) . "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
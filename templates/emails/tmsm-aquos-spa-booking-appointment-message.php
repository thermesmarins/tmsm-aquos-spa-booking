<?php
/**
 * Appointment confirmation processing order email
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Hide prices and totals if total is zero
if($order->get_total() == 0) {
?>
	<style type="text/css">
		table.td thead tr .td:nth-child(3),
		table.td tbody tr .td:nth-child(3),
		table.td tfoot
		{
			display: none;
		}

	</style>

<?php
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'You’ve received the following appointment with a message or appointments had errors, from %s:', 'tmsm-aquos-spa-booking' ), $order->get_formatted_billing_full_name() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

<?php if( ! empty($order->get_customer_note()) ) { ?>
<p><?php printf( esc_html__( 'Message from the customer: %s', 'tmsm-aquos-spa-booking' ), esc_html($order->get_customer_note()) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php } ?>

<?php if( ! empty($notes) ) { ?>
	<p><?php printf( esc_html__( 'Order errors: %s', 'tmsm-aquos-spa-booking' ), '<br>'.$notes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
<?php } ?>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );

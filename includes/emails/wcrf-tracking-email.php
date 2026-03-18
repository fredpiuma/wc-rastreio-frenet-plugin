<?php
/**
 * HTML Template for Tracking Email
 *
 * @package WC_Rastreio_Frenet
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Olá %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<p><?php esc_html_e( 'Seu pedido foi enviado! Confira abaixo o código de rastreio para acompanhar a entrega.', 'wc-rastreio-frenet' ); ?></p>

<h2><?php esc_html_e( 'Código de Rastreio', 'wc-rastreio-frenet' ); ?></h2>

<p style="font-size: 18px; font-weight: bold; background: #f7f7f7; padding: 10px; border: 1px dashed #ccc; display: inline-block;">
	<?php echo esc_html( $tracking_code ); ?>
</p>

<p>
	<a href="<?php echo esc_url( $tracking_url ); ?>" style="background-color: #7f54b3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
		<?php esc_html_e( 'Rastrear Encomenda', 'wc-rastreio-frenet' ); ?>
	</a>
</p>
<p style="font-size: 12px; color: #777;">
    <?php echo esc_html( $tracking_url ); ?>
</p>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
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

?>

<p><?php esc_html_e( 'Obrigado por comprar conosco!', 'woocommerce' ); ?></p>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCRF_Tracking_Display
 *
 * Handles the display of tracking information on the front-end.
 */
class WCRF_Tracking_Display {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Display tracking button before order details section.
		add_action( 'woocommerce_before_template_part', array( $this, 'maybe_display_tracking_button' ), 10, 4 );
	}

	/**
	 * Displays a tracking button before the order details template if a tracking code is available.
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path.
	 * @param string $located       Template location.
	 * @param array  $args          Template arguments.
	 */
	public function maybe_display_tracking_button( $template_name, $template_path, $located, $args ) {
		// We only want to inject before the order-details.php template.
		if ( 'order/order-details.php' !== $template_name ) {
			return;
		}

		// Ensure we have an order object or ID.
		$order = null;
		if ( isset( $args['order_id'] ) ) {
			$order = wc_get_order( $args['order_id'] );
		} elseif ( isset( $args['order'] ) && is_a( $args['order'], 'WC_Order' ) ) {
			$order = $args['order'];
		}

		if ( ! $order ) {
			return;
		}

		// Check if tracking code exists.
		$tracking_code = $order->get_meta( '_wcrf_tracking_code' );
		if ( empty( $tracking_code ) ) {
			return;
		}

		// Generate tracking URL.
		$tracking_url = WCRF_Helper::get_tracking_url( $tracking_code );

		// Output the tracking button.
		?>
		<div class="wcrf-tracking-button-container" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #eee; border-radius: 5px; text-align: center;">
			<p style="margin-bottom: 10px; font-weight: bold;"><?php esc_html_e( 'Seu pedido possui um código de rastreamento:', 'wc-rastreio-frenet' ); ?> <strong><?php echo esc_html( $tracking_code ); ?></strong></p>
			<a href="<?php echo esc_url( $tracking_url ); ?>" class="button" target="_blank" rel="noopener noreferrer" style="background-color: #7f54b3; color: white;">
				<?php esc_html_e( 'Rastrear Pedido', 'wc-rastreio-frenet' ); ?>
			</a>
		</div>
		<?php
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCRF_Admin_Metabox
 *
 * Handles the Order Metabox for Tracking Code.
 */
class WCRF_Admin_Metabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Save data.
		// woocommerce_process_shop_order_meta works for both Legacy (Post) and HPOS (Custom Tables).
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box_data' ), 10, 2 );
	}

	/**
	 * Add the metabox to the order edit screen.
	 */
	public function add_meta_box() {
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'wcrf_tracking_metabox',
			__( 'Rastreio Frenet', 'wc-rastreio-frenet' ),
			array( $this, 'render_meta_box' ),
			$screen,
			'side',
			'high'
		);
	}

	/**
	 * Render the metabox content.
	 *
	 * @param WP_Post|WC_Order $post_or_order_object The post or order object.
	 */
	public function render_meta_box( $post_or_order_object ) {
		// Get Order object consistently.
		$order = ( $post_or_order_object instanceof WC_Order ) ? $post_or_order_object : wc_get_order( $post_or_order_object->ID );

		if ( ! $order ) {
			return;
		}

		// Add nonce for security.
		wp_nonce_field( 'wcrf_save_tracking_data', 'wcrf_tracking_nonce' );

		// Retrieve existing value using WC CRUD.
		$value = $order->get_meta( '_wcrf_tracking_code' );
		?>
		<style>
			.wcrf-metabox-wrapper { margin-top: 10px; }
			.wcrf-metabox-wrapper input { width: 100%; }
		</style>
		<div class="wcrf-metabox-wrapper">
			<p>
				<label for="wcrf_tracking_code"><strong><?php esc_html_e( 'Código de Rastreio:', 'wc-rastreio-frenet' ); ?></strong></label>
			</p>
			<p>
				<input type="text" id="wcrf_tracking_code" name="wcrf_tracking_code" value="<?php echo esc_attr( $value ); ?>" placeholder="Ex: AB123456789BR ou NSWC7NR6">
			</p>
			<p class="description">
				<?php esc_html_e( 'Correios: formato AA000000000AA. Loggi: 8 caracteres alfanuméricos. O link de rastreio é detectado automaticamente. Ao salvar, um e-mail será enviado ao cliente se o código for novo ou alterado.', 'wc-rastreio-frenet' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save the metabox data.
	 *
	 * @param int $post_id The post ID.
	 * @param WP_Post|WC_Order $post The post object (optional).
	 */
	public function save_meta_box_data( $post_id, $post = null ) {
		error_log( '[WC Rastreio Frenet] save_meta_box_data iniciado. ID: ' . $post_id );

		// Check nonce.
		if ( ! isset( $_POST['wcrf_tracking_nonce'] ) || ! wp_verify_nonce( $_POST['wcrf_tracking_nonce'], 'wcrf_save_tracking_data' ) ) {
			error_log( '[WC Rastreio Frenet] Falha no Nonce.' );
			return;
		}

		// Check permissions.
		// In HPOS, post type might be null or handled differently, but capability check is standard.
		if ( ! current_user_can( 'edit_shop_order', $post_id ) ) {
			error_log( '[WC Rastreio Frenet] Sem permissao edit_shop_order.' );
			return;
		}

		// Check if field is present.
		if ( ! isset( $_POST['wcrf_tracking_code'] ) ) {
			error_log( '[WC Rastreio Frenet] Campo wcrf_tracking_code nao enviado.' );
			return;
		}

		// Sanitize and format input.
		$raw_code = sanitize_text_field( $_POST['wcrf_tracking_code'] );
		$clean_code = str_replace( ' ', '', $raw_code );
		$clean_code = mb_strtoupper( $clean_code, 'UTF-8' );

		// Get order object.
		$order = wc_get_order( $post_id );

		if ( ! $order ) {
			error_log( '[WC Rastreio Frenet] Pedido nao encontrado: ' . $post_id );
			return;
		}

		// Get current value.
		$old_code = $order->get_meta( '_wcrf_tracking_code' );

		error_log( '[WC Rastreio Frenet] Comparando. Old: "' . $old_code . '" | New: "' . $clean_code . '"' );

		// Check if changed.
		if ( $clean_code !== $old_code ) {
			// Update meta using WC CRUD.
			$order->update_meta_data( '_wcrf_tracking_code', $clean_code );
			$order->save(); // Persist changes.

			error_log( '[WC Rastreio Frenet] Meta salvo.' );

			// Trigger email manually to ensure it runs.
			if ( ! empty( $clean_code ) ) {
				error_log( '[WC Rastreio Frenet] Tentando buscar instancia de email no Mailer do WC.' );
				
				// Ensure WC Mailer is loaded
				if ( isset( WC()->mailer ) ) {
					WC()->mailer();
				}

				$emails = WC()->mailer()->get_emails();

				if ( isset( $emails['WCRF_Tracking_Email'] ) ) {
					error_log( '[WC Rastreio Frenet] Instancia encontrada. Disparando trigger.' );
					$emails['WCRF_Tracking_Email']->trigger( $order, $clean_code );
				} else {
					error_log( '[WC Rastreio Frenet] ERRO: Instancia WCRF_Tracking_Email nao encontrada no Mailer.' );
				}
			}
		}
	}
}
<?php

/**
 * Plugin Name: WC Rastreio Frenet
 * Plugin URI: https://www.fredericodecastro.com.br
 * Description: Adiciona campo de rastreio no pedido e envia e-mail automático para o cliente com link da Frenet.
 * Version: 1.1.1
 * Author: Frederico de Castro
 * Author URI: https://www.fredericodecastro.com.br
 * Text Domain: wc-rastreio-frenet
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 *
 * @package WC_Rastreio_Frenet
 */

if (! defined('ABSPATH')) {
	exit;
}

// Define plugin constants.
define('WCRF_VERSION', '1.0.0');
define('WCRF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCRF_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class.
 */
class WCRF_Plugin
{

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Declare HPOS compatibility.
		add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));

		// Hook into plugins_loaded to ensuring WC is active.
		add_action('plugins_loaded', array($this, 'init'));
	}

	/**
	 * Declare HPOS compatibility.
	 */
	public function declare_hpos_compatibility()
	{
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		}
	}

	/**
	 * Initialize the plugin.
	 */
	public function init()
	{
		// Check if WooCommerce is active.
		if (! class_exists('WooCommerce')) {
			add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
			return;
		}

		// Load includes.
		$this->includes();

		// Init Metabox.
		new WCRF_Admin_Metabox();

		// Init Front-end Display.
		new WCRF_Tracking_Display();

		// Register Email Class.
		add_filter('woocommerce_email_classes', array($this, 'register_email_class'));
	}

	/**
	 * Load necessary files.
	 */
	private function includes()
	{
		require_once WCRF_PLUGIN_DIR . 'includes/class-wcrf-helper.php';
		require_once WCRF_PLUGIN_DIR . 'includes/class-wcrf-admin-metabox.php';
		require_once WCRF_PLUGIN_DIR . 'includes/class-wcrf-tracking-display.php';
		// Email class is loaded via the filter callback to avoid early loading issues.
	}

	/**
	 * Register the custom email class.
	 *
	 * @param array $email_classes Existing email classes.
	 * @return array Modified email classes.
	 */
	public function register_email_class($email_classes)
	{
		require_once WCRF_PLUGIN_DIR . 'includes/class-wcrf-email.php';
		$email_classes['WCRF_Tracking_Email'] = new WCRF_Tracking_Email();
		return $email_classes;
	}

	/**
	 * Admin notice if WooCommerce is missing.
	 */
	public function woocommerce_missing_notice()
	{
?>
		<div class="error">
			<p><?php esc_html_e('WC Rastreio Frenet requer que o WooCommerce esteja instalado e ativo.', 'wc-rastreio-frenet'); ?></p>
		</div>
<?php
	}
}

new WCRF_Plugin();

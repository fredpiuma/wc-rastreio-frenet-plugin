<?php
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Class WCRF_Tracking_Email
 *
 * Custom Email for Frenet Tracking Code.
 */
class WCRF_Tracking_Email extends WC_Email
{

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->id             = 'wcrf_tracking_email';
		$this->title          = __('Código de Rastreio Frenet', 'wc-rastreio-frenet');
		$this->description    = __('E-mail enviado ao cliente quando o código de rastreio é adicionado ou atualizado.', 'wc-rastreio-frenet');
		$this->template_html  = 'emails/wcrf-tracking-email.php';
		$this->template_plain = 'emails/plain/wcrf-tracking-email.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Trigger on custom action.
		add_action('wcrf_tracking_code_updated', array($this, 'trigger'), 10, 2);

		// Call parent constructor.
		parent::__construct();

		// Set the default template path to this plugin's directory.
		$this->template_base = WCRF_PLUGIN_DIR . 'includes/';
	}

	/**
	 * Trigger the email.
	 *
	 * @param int|WC_Order $order_id      The order ID or WC_Order object.
	 * @param string       $tracking_code The tracking code.
	 */
	public function trigger($order_id, $tracking_code = '')
	{
		$this->setup_locale();

		if (is_a($order_id, 'WC_Order')) {
			$order = $order_id;
		} else {
			$order = wc_get_order($order_id);
		}

		if (is_a($order, 'WC_Order')) {
			$this->object                         = $order;
			$this->placeholders['{order_date}']   = wc_format_datetime($this->object->get_date_created());
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
			$this->recipient                      = $this->object->get_billing_email();
		}

		// If no tracking code provided (e.g. manual trigger test), try to fetch it.
		if (empty($tracking_code) && is_a($order, 'WC_Order')) {
			$tracking_code = $order->get_meta('_wcrf_tracking_code');
		}

		// Pass tracking code to template.
		$this->tracking_code = $tracking_code;
		$this->tracking_url  = WCRF_Helper::get_tracking_url($tracking_code);

		if ($this->is_enabled() && $this->get_recipient()) {
			$this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 */
	public function get_content_html()
	{
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
				'tracking_code' => $this->tracking_code,
				'tracking_url'  => $this->tracking_url,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 */
	public function get_content_plain()
	{
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
				'tracking_code' => $this->tracking_code,
				'tracking_url'  => $this->tracking_url,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Initialize settings form fields.
	 */
	public function init_form_fields()
	{
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf(__('Available placeholders: %s', 'woocommerce'), '<code>' . implode('</code>, <code>', array_keys($this->placeholders)) . '</code>');
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __('Enable/Disable', 'woocommerce'),
				'type'    => 'checkbox',
				'label'   => __('Enable this email notification', 'woocommerce'),
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => __('Subject', 'woocommerce'),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading' => array(
				'title'       => __('Email heading', 'woocommerce'),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __('Email type', 'woocommerce'),
				'type'        => 'select',
				'description' => __('Choose which format of email to send.', 'woocommerce'),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => array(
					'plain'     => __('Plain text', 'woocommerce'),
					'html'      => __('HTML', 'woocommerce'),
					'multipart' => __('Multipart', 'woocommerce'),
				),
			),
		);
	}

	/**
	 * Default Subject.
	 */
	public function get_default_subject()
	{
		return __('Seu pedido #{order_number} foi enviado — código de rastreio', 'wc-rastreio-frenet');
	}

	/**
	 * Default Heading.
	 */
	public function get_default_heading()
	{
		return __('Rastreio do seu pedido', 'wc-rastreio-frenet');
	}
}

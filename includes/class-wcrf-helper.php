<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for WC Rastreio Frenet.
 */
class WCRF_Helper {

	/**
	 * Detect carrier from tracking code and return the appropriate tracking URL.
	 *
	 * - Loggi:    8 uppercase alphanumeric chars (e.g. NSWC7NR6)  → /LOG/
	 * - Correios: standard postal format AA000000000AA (13 chars)  → /COR/
	 *
	 * @param string $tracking_code The tracking code.
	 * @return string Full tracking URL.
	 */
	public static function get_tracking_url( $tracking_code ) {
		// Normalize tracking code.
		$tracking_code = strtoupper( trim( $tracking_code ) );

		if ( preg_match( '/^[A-Z0-9]{8}$/', $tracking_code ) ) {
			$endpoint = 'LOG';
		} else {
			$endpoint = 'COR';
		}

		return 'https://rastreio.frenet.com.br/' . $endpoint . '/' . $tracking_code;
	}
}

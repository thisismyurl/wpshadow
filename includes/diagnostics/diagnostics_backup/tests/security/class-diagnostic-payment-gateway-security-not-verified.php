<?php
/**
 * Payment Gateway Security Not Verified Diagnostic
 *
 * Checks if payment gateways are secure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment Gateway Security Not Verified Diagnostic Class
 *
 * Detects insecure payment gateways.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Payment_Gateway_Security_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'payment-gateway-security-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Payment Gateway Security Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if payment gateways are secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for WooCommerce payment gateways
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check if payment gateway is SSL-ready
		if ( ! is_ssl() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Payment gateways require SSL/HTTPS. Your site is not using HTTPS. Enable SSL before processing payments.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/payment-gateway-security-not-verified',
			);
		}

		return null;
	}
}

<?php
/**
 * WooCommerce SSL/HTTPS Not Enforced Diagnostic
 *
 * Checks if WooCommerce enforces HTTPS for checkout.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce SSL/HTTPS Not Enforced Diagnostic Class
 *
 * Detects missing HTTPS enforcement in WooCommerce.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_SSL_HTTPS_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-ssl-https-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce SSL/HTTPS Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WooCommerce enforces HTTPS';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check if WooCommerce is enforcing HTTPS on checkout
		if ( 'yes' !== get_option( 'woocommerce_force_ssl_checkout', 'no' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WooCommerce checkout does not force HTTPS. Customer payment data is not encrypted, violating PCI-DSS compliance.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-ssl-https-not-enforced',
			);
		}

		return null;
	}
}

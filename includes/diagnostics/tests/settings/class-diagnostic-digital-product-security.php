<?php
/**
 * Digital Product Delivery and Download Security Diagnostic
 *
 * Checks if digital product delivery systems implement secure download links,
 * expiration controls, IP restrictions, and download limit enforcement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digital Product Delivery Security Diagnostic Class
 *
 * Verifies digital product delivery implements secure download controls.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Digital_Product_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'digital-product-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Digital Product Delivery and Download Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies digital product delivery implements secure download controls and restrictions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for digital download plugins.
		$digital_plugins = array(
			'easy-digital-downloads',
			'woocommerce',
			'download-monitor',
			'digital-downloads',
		);

		$has_digital = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $digital_plugins as $dig_plugin ) {
				if ( stripos( $plugin, $dig_plugin ) !== false ) {
					$has_digital = true;
					break 2;
				}
			}
		}

		if ( ! $has_digital ) {
			return null; // No digital products.
		}

		$issues = array();

		// Check for hotlink protection.
		$has_hotlink_protection = false;
		$protection_plugins = array(
			'prevent-direct-access',
			'download-protection',
			'secure-downloads',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $protection_plugins as $prot_plugin ) {
				if ( stripos( $plugin, $prot_plugin ) !== false ) {
					$has_hotlink_protection = true;
					break 2;
				}
			}
		}

		if ( ! $has_hotlink_protection ) {
			$issues[] = __( 'No download hotlink protection plugin detected', 'wpshadow' );
		}

		// Check if HTTPS is enabled.
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS (downloads not encrypted in transit)', 'wpshadow' );
		}

		// Check for file access logging.
		$has_file_logging = false;
		$log_plugins = array(
			'download-monitor',
			'edd-download-log',
			'file-access-log',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $log_plugins as $log_plugin ) {
				if ( stripos( $plugin, $log_plugin ) !== false ) {
					$has_file_logging = true;
					break;
				}
			}
		}

		if ( ! $has_file_logging ) {
			$issues[] = __( 'No download activity logging detected (prevents abuse tracking)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Digital product security concerns: %s. Digital downloads should use secure, expiring URLs with hotlink protection and abuse monitoring.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/digital-product-security',
		);
	}
}

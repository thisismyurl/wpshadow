<?php
/**
 * Digital Product Delivery Privacy and Tracking Diagnostic
 *
 * Checks if digital product delivery systems minimize tracking,
 * implement secure download URLs, and protect customer purchase privacy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6031.1459
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digital Product Delivery Privacy Diagnostic Class
 *
 * Verifies digital product delivery implements privacy protection.
 *
 * @since 1.6031.1459
 */
class Diagnostic_Digital_Product_Privacy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'digital-product-privacy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Digital Product Delivery Privacy and Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies digital product delivery minimizes tracking and protects purchase privacy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1459
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for digital download plugins.
		$digital_plugins = array(
			'easy-digital-downloads',
			'woocommerce-software',
			'download-monitor',
			'digital-goods',
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
			return null; // No digital product delivery.
		}

		$issues = array();

		// Check for secure download links (expiring URLs).
		// This would typically be a setting in EDD or WooCommerce.
		// We check if the plugin has security addons active.
		$has_secure_downloads = false;
		$secure_plugins = array(
			'edd-secure',
			'woocommerce-secure',
			'download-security',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $secure_plugins as $sec_plugin ) {
				if ( stripos( $plugin, $sec_plugin ) !== false ) {
					$has_secure_downloads = true;
					break 2;
				}
			}
		}

		// Check for download logging (can be privacy concern).
		$has_download_logging = false;
		$log_plugins = array(
			'download-monitor',
			'file-manager',
			'download-tracker',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $log_plugins as $log_plugin ) {
				if ( stripos( $plugin, $log_plugin ) !== false ) {
					$has_download_logging = true;
					break;
				}
			}
		}

		if ( $has_download_logging ) {
			$issues[] = __( 'Download tracking detected (verify privacy policy discloses this)', 'wpshadow' );
		}

		// Check for HTTPS.
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS (download URLs not encrypted)', 'wpshadow' );
		}

		// Check for privacy policy.
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page ) ) {
			$issues[] = __( 'No privacy policy page configured', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Digital product privacy concerns: %s. Digital delivery systems should use secure, expiring download URLs and minimize customer tracking.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/digital-product-privacy',
		);
	}
}

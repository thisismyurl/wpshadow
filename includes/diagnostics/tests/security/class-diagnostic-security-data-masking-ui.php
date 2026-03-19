<?php
/**
 * Diagnostic: Data Masking in UI
 *
 * Checks for credit card masking, password field types, and sensitive data
 * exposure in HTML. Prevents data leakage through UI.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4008
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Masking in UI Diagnostic
 *
 * Verifies password fields use type='password', checks for credit card masking,
 * and scans for sensitive data in HTML comments.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Security_Data_Masking_UI extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-data-masking-ui';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Masking in UI';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper masking of passwords and sensitive data in HTML output';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check for data masking issues.
	 *
	 * Analyzes theme templates and plugin files for:
	 * - Password fields without type='password'
	 * - Credit card numbers in HTML
	 * - Sensitive data in HTML comments
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check active theme templates.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();

		// Get PHP files from theme.
		$theme_files = array();
		if ( is_dir( $theme_path ) ) {
			$theme_files = glob( $theme_path . '/*.php' );
			if ( is_dir( $theme_path . '/templates' ) ) {
				$theme_files = array_merge( $theme_files, glob( $theme_path . '/templates/*.php' ) );
			}
		}

		foreach ( $theme_files as $file ) {
			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check for password inputs without type='password'.
			if ( preg_match( '/<input[^>]*(password|pass|pwd)[^>]*type=["\']text["\']/i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: file name */
					__( 'Password field using type="text" in %s', 'wpshadow' ),
					basename( $file )
				);
			}
			
			// Check for credit card patterns in HTML.
			if ( preg_match( '/[0-9]{4}[- ]?[0-9]{4}[- ]?[0-9]{4}[- ]?[0-9]{4}/', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: file name */
					__( 'Potential credit card number in %s', 'wpshadow' ),
					basename( $file )
				);
			}
			
			// Check for sensitive data in HTML comments.
			if ( preg_match( '/<!--[^>]*(password|api_key|secret|token|credit)/i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: file name */
					__( 'Sensitive data in HTML comments in %s', 'wpshadow' ),
					basename( $file )
				);
			}
		}

		// Check WooCommerce credit card handling if active.
		if ( class_exists( 'WooCommerce' ) ) {
			// Check if payment gateways properly mask card data.
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			
			foreach ( $gateways as $gateway ) {
				// Check if gateway stores card data.
				if ( property_exists( $gateway, 'supports' ) && is_array( $gateway->supports ) ) {
					if ( in_array( 'tokenization', $gateway->supports, true ) ) {
						// Gateway supports tokenization - should mask card numbers.
						// This is acceptable.
						continue;
					}
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__(
					'Data masking issues detected: %s. Sensitive data should never be displayed in plaintext. Use type="password" for password fields, mask credit cards to last 4 digits, and avoid sensitive data in HTML comments.',
					'wpshadow'
				),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/data-masking-ui',
		);
	}
}

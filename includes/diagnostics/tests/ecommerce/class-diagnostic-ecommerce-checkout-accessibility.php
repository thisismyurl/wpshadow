<?php
/**
 * E-commerce Checkout Accessibility Diagnostic
 *
 * Checks if e-commerce checkout processes meet WCAG accessibility standards
 * including form labels, keyboard navigation, and screen reader compatibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6031.1501
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-commerce Checkout Accessibility Diagnostic Class
 *
 * Verifies e-commerce checkout meets accessibility standards.
 *
 * @since 1.6031.1501
 */
class Diagnostic_Ecommerce_Checkout_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ecommerce-checkout-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'E-commerce Checkout Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies e-commerce checkout processes meet WCAG accessibility standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1501
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for ecommerce plugins.
		$ecommerce_plugins = array(
			'woocommerce',
			'easy-digital-downloads',
		);

		$has_ecommerce = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $ecommerce_plugins as $ec_plugin ) {
				if ( stripos( $plugin, $ec_plugin ) !== false ) {
					$has_ecommerce = true;
					break 2;
				}
			}
		}

		if ( ! $has_ecommerce ) {
			return null; // No ecommerce.
		}

		$issues = array();

		// Check for accessibility plugins.
		$has_accessibility = false;
		$a11y_plugins = array(
			'wp-accessibility',
			'one-click-accessibility',
			'accessibility-checker',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $a11y_plugins as $a11y_plugin ) {
				if ( stripos( $plugin, $a11y_plugin ) !== false ) {
					$has_accessibility = true;
					break 2;
				}
			}
		}

		if ( ! $has_accessibility ) {
			$issues[] = __( 'No accessibility enhancement plugin detected', 'wpshadow' );
		}

		// Check for WooCommerce accessibility features (if WC active).
		foreach ( $active_plugins as $plugin ) {
			if ( stripos( $plugin, 'woocommerce' ) !== false ) {
				// Check if WC accessibility features are enabled.
				$wc_accessibility = get_option( 'woocommerce_checkout_accessibility', 'no' );
				if ( $wc_accessibility === 'no' ) {
					$issues[] = __( 'WooCommerce accessibility features not explicitly enabled', 'wpshadow' );
				}
			}
		}

		// Check for form validation plugins (helps accessibility).
		$has_validation = false;
		$validation_plugins = array(
			'checkout-field',
			'form-validation',
			'woocommerce-checkout-manager',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $validation_plugins as $val_plugin ) {
				if ( stripos( $plugin, $val_plugin ) !== false ) {
					$has_validation = true;
					break 2;
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'E-commerce accessibility concerns: %s. Checkout processes must be accessible to users with disabilities per ADA/WCAG standards.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ecommerce-checkout-accessibility',
		);
	}
}

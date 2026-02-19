<?php
/**
 * E-commerce Checkout Accessibility Diagnostic
 *
 * Verifies checkout process meets accessibility standards
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_EcommerceCheckoutAccessibility Class
 *
 * Checks for WCAG compliance, keyboard navigation, screen reader support
 *
 * @since 1.6031.1445
 */
class Diagnostic_EcommerceCheckoutAccessibility extends Diagnostic_Base {

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
protected static $description = 'Verifies checkout process meets accessibility standards';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'ecommerce';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for ecommerce plugins.
		if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return null;
		}

		$issues = array();

		// Check for accessibility plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$a11y_plugins = array( 'accessibility', 'wp-accessibility', 'one-click-accessibility' );
		$has_a11y = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $a11y_plugins as $a11y_plugin ) {
				if ( stripos( $plugin, $a11y_plugin ) !== false ) {
					$has_a11y = true;
					break 2;
				}
			}
		}

		if ( ! $has_a11y ) {
			$issues[] = __( 'No accessibility enhancement plugin detected', 'wpshadow' );
		}

		// Check for screen reader optimization plugins.
		$sr_plugins = array( 'screen-reader', 'aria', 'accessible-reading' );
		$has_sr = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $sr_plugins as $sr_plugin ) {
				if ( stripos( $plugin, $sr_plugin ) !== false ) {
					$has_sr = true;
					break 2;
				}
			}
		}

		if ( ! $has_sr ) {
			$issues[] = __( 'No screen reader optimization detected', 'wpshadow' );
		}

		// Check WooCommerce-specific settings.
		if ( class_exists( 'WooCommerce' ) ) {
			// Check if checkout requires account.
			$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'yes' );
			if ( 'no' === $guest_checkout ) {
				$issues[] = __( 'Guest checkout disabled (accessibility barrier)', 'wpshadow' );
			}
		}

		// Check for keyboard navigation plugins.
		$keyboard_plugins = array( 'keyboard-navigation', 'skip-to', 'accessibility-toolbar' );
		$has_keyboard = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $keyboard_plugins as $kb_plugin ) {
				if ( stripos( $plugin, $kb_plugin ) !== false ) {
					$has_keyboard = true;
					break 2;
				}
			}
		}

		if ( ! $has_keyboard ) {
			$issues[] = __( 'No keyboard navigation enhancement plugin', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Checkout accessibility concerns: %s. Ecommerce checkout must be accessible to all users.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ecommerce-checkout-accessibility',
		);
	}
}

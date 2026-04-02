<?php
/**
 * Checkout Purchase Flow Optimized Diagnostic
 *
 * Tests if purchase flow is streamlined and optimized.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Purchase Flow Optimized Diagnostic Class
 *
 * Evaluates if checkout flow has excessive fields or friction.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_Checkout_Flow extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-checkout-flow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Checkout Purchase Flow Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if purchase flow is streamlined and optimized';

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
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$checkout_page_id = wc_get_page_id( 'checkout' );
		if ( $checkout_page_id <= 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Checkout page not configured. Set a checkout page in WooCommerce settings.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-flow-optimized',
				'persona'      => 'ecommerce',
			);
		}

		$fields = WC()->checkout()->get_checkout_fields();
		$field_count = 0;
		foreach ( $fields as $section_fields ) {
			$field_count += is_array( $section_fields ) ? count( $section_fields ) : 0;
		}

		$optimization_plugins = array(
			'woocommerce-checkout-field-editor/woocommerce-checkout-field-editor.php',
			'checkout-field-editor-for-woocommerce/checkout-field-editor-for-woocommerce.php',
			'cartflows/cartflows.php',
		);

		$has_optimizer = false;
		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_optimizer = true;
				break;
			}
		}

		$guest_checkout = get_option( 'woocommerce_enable_guest_checkout' );
		if ( 'no' === $guest_checkout ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Guest checkout disabled. Requiring accounts can reduce conversion for first-time buyers.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-flow-optimized',
				'persona'      => 'ecommerce',
			);
		}

		if ( $field_count > 25 && ! $has_optimizer ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of fields */
					__( 'Checkout has %d fields. Reduce fields or use a checkout optimizer to improve completion rate.', 'wpshadow' ),
					$field_count
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-flow-optimized',
				'persona'      => 'ecommerce',
				'meta'         => array(
					'field_count' => $field_count,
				),
			);
		}

		return null;
	}
}

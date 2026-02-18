<?php
/**
 * Missing WooCommerce Data in GDPR Export Diagnostic
 *
 * Tests WooCommerce order, subscription, and customer data inclusion in GDPR exports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_WooCommerce_Data_In_GDPR_Export Class
 *
 * Verifies that WooCommerce data is included in GDPR exports.
 *
 * @since 1.2034.1445
 */
class Diagnostic_Missing_WooCommerce_Data_In_GDPR_Export extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-woocommerce-data-in-gdpr-export';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce GDPR Export Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WooCommerce data is properly included in GDPR personal data exports';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			// WooCommerce not active - not applicable.
			return null;
		}

		$issues = array();

		// 1. Check if WooCommerce registers data exporters.
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );
		
		$wc_exporters = array(
			'customer',
			'orders',
			'downloads',
		);

		$missing_exporters = array();
		foreach ( $wc_exporters as $exporter_type ) {
			$found = false;
			foreach ( $exporters as $exporter_id => $exporter ) {
				if ( isset( $exporter['exporter_friendly_name'] ) ) {
					$exporter_name = strtolower( $exporter['exporter_friendly_name'] );
					if ( false !== strpos( $exporter_name, 'woocommerce' ) && 
					     false !== strpos( $exporter_name, $exporter_type ) ) {
						$found = true;
						break;
					}
				}
			}

			if ( ! $found ) {
				$missing_exporters[] = $exporter_type;
			}
		}

		if ( ! empty( $missing_exporters ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing exporters */
				__( 'Missing WooCommerce exporters: %s', 'wpshadow' ),
				implode( ', ', $missing_exporters )
			);
		}

		// 2. Check WooCommerce version (privacy features added in 3.4.0).
		if ( defined( 'WC_VERSION' ) ) {
			if ( version_compare( WC_VERSION, '3.4.0', '<' ) ) {
				$issues[] = sprintf(
					/* translators: %s: WooCommerce version */
					__( 'WooCommerce version %s does not include GDPR export features (requires 3.4.0+)', 'wpshadow' ),
					WC_VERSION
				);
			}
		}

		// 3. Check if orders exist (to verify integration is needed).
		global $wpdb;
		$order_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'shop_order'
			)
		);

		if ( (int) $order_count === 0 ) {
			// No orders yet - can't test properly.
			return null;
		}

		// 4. Check for subscription data if plugin active.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$subscription_exporter_found = false;
			foreach ( $exporters as $exporter_id => $exporter ) {
				if ( isset( $exporter['exporter_friendly_name'] ) &&
				     false !== strpos( strtolower( $exporter['exporter_friendly_name'] ), 'subscription' ) ) {
					$subscription_exporter_found = true;
					break;
				}
			}

			if ( ! $subscription_exporter_found ) {
				$issues[] = __( 'WooCommerce Subscriptions active but no subscription data exporter found', 'wpshadow' );
			}
		}

		// 5. Check if payment method data is being handled.
		$payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
		if ( ! empty( $payment_gateways ) ) {
			// Payment methods store sensitive data - should be exported.
			$has_payment_exporter = false;
			foreach ( $exporters as $exporter_id => $exporter ) {
				if ( isset( $exporter['callback'] ) && is_callable( $exporter['callback'] ) ) {
					// WooCommerce customer exporter includes payment methods.
					if ( isset( $exporter['exporter_friendly_name'] ) &&
					     ( false !== strpos( strtolower( $exporter['exporter_friendly_name'] ), 'customer' ) ||
					       false !== strpos( strtolower( $exporter['exporter_friendly_name'] ), 'payment' ) ) ) {
						$has_payment_exporter = true;
						break;
					}
				}
			}

			if ( ! $has_payment_exporter ) {
				$issues[] = __( 'Payment gateway data may not be included in exports', 'wpshadow' );
			}
		}

		// 6. Check for custom WooCommerce tables (HPOS).
		if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			if ( method_exists( 'Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled' ) ) {
				if ( \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS is enabled - verify exporters handle custom tables.
					$issues[] = __( 'High-Performance Order Storage (HPOS) is enabled - verify exporters include custom table data', 'wpshadow' );
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
				__( 'WooCommerce GDPR export issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/woocommerce-gdpr-export',
			'details'      => array(
				'issues'           => $issues,
				'wc_version'       => defined( 'WC_VERSION' ) ? WC_VERSION : 'unknown',
				'order_count'      => $order_count,
				'exporters_count'  => count( $exporters ),
			),
		);
	}
}

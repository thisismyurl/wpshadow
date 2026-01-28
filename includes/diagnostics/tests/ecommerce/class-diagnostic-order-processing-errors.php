<?php
/**
 * Order Processing Errors Diagnostic
 *
 * Monitors WooCommerce orders for processing failures and payment
 * issues that indicate systemic problems or security concerns.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Order_Processing_Errors Class
 *
 * Monitors WooCommerce order processing for errors and failures.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Order_Processing_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'order-processing-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Order Processing Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors for WooCommerce order processing failures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if order processing issues found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not an e-commerce site
		}

		$order_errors = self::check_order_errors();

		if ( $order_errors['error_count'] === 0 ) {
			return null; // No recent errors
		}

		$severity = $order_errors['error_count'] > 10 ? 'critical' : 'high';
		$threat   = $order_errors['error_count'] > 10 ? 85 : 70;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of failed orders */
				__( '%d orders have processing errors in the past 7 days. Payment gateway issues, plugin conflicts, or server errors preventing order completion.', 'wpshadow' ),
				$order_errors['error_count']
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/woocommerce-order-errors',
			'family'       => self::$family,
			'meta'         => array(
				'failed_orders_7days'   => $order_errors['error_count'],
				'failed_orders_30days'  => $order_errors['error_count_30days'],
				'revenue_impact'        => __( 'Each failed order = lost $50-500+ in revenue' ),
				'customer_churn_risk'   => __( 'Failed checkouts increase cart abandonment 5-10x' ),
			),
			'details'      => array(
				'common_causes'          => array(
					'Payment Gateway Timeout' => array(
						'Cause: Slow network, processing delay',
						'Fix: Review gateway logs, retry mechanism',
						'Impact: Customer retries = double charges',
					),
					'Plugin Conflict' => array(
						'Cause: Incompatible plugin hooks',
						'Fix: Disable plugins one-by-one to isolate',
						'Impact: Order blocked or incomplete',
					),
					'Server Issues' => array(
						'Cause: Out of memory, max execution time',
						'Fix: Increase PHP memory/timeout limits',
						'Impact: Orders stuck in processing',
					),
					'SSL/TLS Error' => array(
						'Cause: Payment gateway certificate validation',
						'Fix: Update SSL, verify gateway URL',
						'Impact: Payment gateway unreachable',
					),
				),
				'troubleshooting_steps'   => array(
					'Step 1: Check Payment Gateway Status' => array(
						'Log into payment processor (Stripe, PayPal, etc)',
						'Check for API errors or outages',
						'Review error logs for recent transactions',
					),
					'Step 2: Review WooCommerce Logs' => array(
						'Go to WooCommerce → Logs',
						'Filter for recent date range',
						'Look for payment gateway errors',
					),
					'Step 3: Disable Plugins One-by-One' => array(
						'Disable all non-essential plugins',
						'Test checkout with just WooCommerce',
						'Re-enable plugins one-by-one',
						'Identify conflicting plugin',
					),
					'Step 4: Check Server Resources' => array(
						'Review hosting control panel',
						'Check PHP memory limit (256MB minimum)',
						'Check max execution time (30 seconds minimum)',
						'Review error logs for memory exhaustion',
					),
					'Step 5: Test with Sample Transaction' => array(
						'Use test payment details',
						'Monitor order status through completion',
						'Check that order status changes correctly',
						'Verify customer receives email confirmation',
					),
				),
				'prevention_measures'    => array(
					__( 'Monitor orders daily for stuck/failed status' ),
					__( 'Set up alerts for payment failures' ),
					__( 'Log all payment gateway interactions' ),
					__( 'Test checkout process weekly' ),
					__( 'Use Stripe/PayPal test mode for testing' ),
					__( 'Implement order retry mechanism' ),
					__( 'Notify customers of payment failures immediately' ),
				),
				'debugging_tools'        => array(
					'WooCommerce Debug' => array(
						'Define in wp-config.php:',
						'define( "WP_DEBUG", true );',
						'define( "WC_LOG_HANDLER", "file" );',
						'View logs at wp-content/debug.log',
					),
					'Payment Gateway Logs' => array(
						'Stripe: Log in to Dashboard → Developers → Logs',
						'PayPal: Log in to Business Account → Activity',
						'Most gateways have API logs available',
					),
				),
			),
		);
	}

	/**
	 * Check for order processing errors.
	 *
	 * @since  1.2601.2148
	 * @return array Order error statistics.
	 */
	private static function check_order_errors() {
		global $wpdb;

		$days_ago_7  = gmdate( 'Y-m-d H:i:s', time() - ( 7 * 24 * 60 * 60 ) );
		$days_ago_30 = gmdate( 'Y-m-d H:i:s', time() - ( 30 * 24 * 60 * 60 ) );

		// Get failed orders in last 7 days
		$failed_7days = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type = 'shop_order'
				AND post_status IN ('wc-failed', 'wc-cancelled', 'wc-on-hold')
				AND post_date > %s",
				$days_ago_7
			)
		);

		// Get failed orders in last 30 days
		$failed_30days = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type = 'shop_order'
				AND post_status IN ('wc-failed', 'wc-cancelled', 'wc-on-hold')
				AND post_date > %s",
				$days_ago_30
			)
		);

		return array(
			'error_count'       => (int) $failed_7days,
			'error_count_30days' => (int) $failed_30days,
		);
	}
}

<?php
/**
 * Payment Failure Rate Diagnostic
 *
 * Checks if failed transactions are being tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment Failure Rate Diagnostic Class
 *
 * Verifies that payment failures are being tracked and that the
 * failure rate is within acceptable limits.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Payment_Failure_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'payment-failure-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Payment Failure Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if failed transactions are being tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the payment failure rate diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if payment issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping payment check', 'wpshadow' );
			return null;
		}

		// Get payment methods.
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$stats['total_payment_methods'] = count( $payment_methods );
		$stats['enabled_payment_methods'] = 0;

		$enabled_methods = array();
		foreach ( $payment_methods as $method ) {
			if ( $method->enabled === 'yes' ) {
				$stats['enabled_payment_methods']++;
				$enabled_methods[] = $method->title;
			}
		}

		if ( $stats['enabled_payment_methods'] === 0 ) {
			$issues[] = __( 'No payment methods enabled', 'wpshadow' );
		}

		// Get failed orders (last 30 days).
		$thirty_days_ago = strtotime( '-30 days' );
		$failed_orders = wc_get_orders( array(
			'status'         => 'failed',
			'date_created'   => '>' . $thirty_days_ago,
			'posts_per_page' => -1,
		) );

		$stats['failed_orders_30_days'] = count( $failed_orders );

		// Get all orders (last 30 days).
		$all_orders = wc_get_orders( array(
			'date_created'   => '>' . $thirty_days_ago,
			'posts_per_page' => -1,
		) );

		$stats['total_orders_30_days'] = count( $all_orders );

		// Calculate failure rate.
		if ( count( $all_orders ) > 0 ) {
			$failure_rate = ( count( $failed_orders ) / count( $all_orders ) ) * 100;
			$stats['failure_rate_percent'] = round( $failure_rate, 2 );

			// Typical failure rate should be <2%.
			if ( $failure_rate > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage */
					__( 'Payment failure rate is %d%% (should be <2%%)', 'wpshadow' ),
					intval( $failure_rate )
				);
			} elseif ( $failure_rate > 5 ) {
				$warnings[] = sprintf(
					/* translators: %d: percentage */
					__( 'Payment failure rate is %d%% - investigate cause', 'wpshadow' ),
					intval( $failure_rate )
				);
			}
		}

		// Check for cancelled orders (may indicate payment issues).
		$cancelled_orders = wc_get_orders( array(
			'status'         => 'cancelled',
			'date_created'   => '>' . $thirty_days_ago,
			'posts_per_page' => -1,
		) );

		$stats['cancelled_orders_30_days'] = count( $cancelled_orders );

		// Check payment method failures.
		$failure_methods = array();
		foreach ( $failed_orders as $order ) {
			$method = $order->get_payment_method_title();
			if ( $method ) {
				$failure_methods[ $method ] = ( $failure_methods[ $method ] ?? 0 ) + 1;
			}
		}

		$stats['failure_by_method'] = array_slice( $failure_methods, 0, 3 );

		// Check if a specific method has high failure rate.
		if ( ! empty( $failure_methods ) ) {
			arsort( $failure_methods );
			$top_failing_method = key( $failure_methods );
			$top_failures = reset( $failure_methods );

			if ( count( $all_orders ) > 0 ) {
				$method_failure_rate = ( $top_failures / count( $all_orders ) ) * 100;
				if ( $method_failure_rate > 5 ) {
					$warnings[] = sprintf(
						/* translators: %s: method name, %d: percentage */
						__( '%s has high failure rate (%d%%) - check configuration', 'wpshadow' ),
						$top_failing_method,
						intval( $method_failure_rate )
					);
				}
			}
		}

		// Check payment gateway SSL certificate.
		$store_url = get_site_url();
		if ( strpos( $store_url, 'https://' ) === false ) {
			$warnings[] = __( 'Store not using HTTPS - required for secure payments', 'wpshadow' );
		}

		// Check for WooCommerce log errors related to payments.
		$log_dir = WC_LOG_DIR;
		if ( file_exists( $log_dir ) ) {
			$log_files = glob( $log_dir . '/*.log' );
			$payment_error_count = 0;

			foreach ( $log_files as $file ) {
				$content = file_get_contents( $file );
				if ( preg_match( '/(payment|gateway|transaction).*error/i', $content ) ) {
					$payment_error_count++;
				}
			}

			$stats['payment_error_logs'] = $payment_error_count;

			if ( $payment_error_count > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: count */
					__( '%d payment error logs detected', 'wpshadow' ),
					$payment_error_count
				);
			}
		}

		// Check PCI compliance.
		$card_storage = get_option( 'woocommerce_store_credit_card' );
		if ( $card_storage === 'yes' ) {
			$warnings[] = __( 'Store configured to store credit cards locally - not PCI compliant', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Payment failures have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-failure-rate',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Payment failures have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-failure-rate',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Payment failure rate is healthy.
	}
}

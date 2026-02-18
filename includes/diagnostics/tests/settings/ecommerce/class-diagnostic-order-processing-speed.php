<?php
/**
 * Order Processing Speed Diagnostic
 *
 * Checks if orders process within SLA.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Processing Speed Diagnostic Class
 *
 * Verifies that orders are being processed quickly and that
 * order fulfillment SLAs are being met.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Order_Processing_Speed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'order-processing-speed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Order Processing Speed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if orders process within SLA';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the order processing speed diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if order processing issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping order processing check', 'wpshadow' );
			return null;
		}

		// Get recent orders.
		$recent_orders = wc_get_orders( array(
			'limit'      => 50,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'status'     => array( 'processing', 'completed', 'pending' ),
		) );

		$stats['recent_orders_count'] = count( $recent_orders );

		if ( empty( $recent_orders ) ) {
			$warnings[] = __( 'No recent orders - cannot assess processing speed', 'wpshadow' );
			return null;
		}

		// Calculate average processing time.
		$processing_times = array();

		foreach ( $recent_orders as $order ) {
			$order_date = $order->get_date_created();
			$completed_date = $order->get_date_completed();

			if ( $order_date && $completed_date ) {
				$processing_time = ( $completed_date->getTimestamp() - $order_date->getTimestamp() ) / 3600; // hours.
				$processing_times[] = $processing_time;
			}
		}

		if ( ! empty( $processing_times ) ) {
			$avg_processing_time = array_sum( $processing_times ) / count( $processing_times );
			$max_processing_time = max( $processing_times );
			$min_processing_time = min( $processing_times );

			$stats['avg_processing_hours'] = round( $avg_processing_time, 1 );
			$stats['max_processing_hours'] = round( $max_processing_time, 1 );
			$stats['min_processing_hours'] = round( $min_processing_time, 1 );

			// Check SLA (typically 24-48 hours).
			$sla_hours = get_option( 'woocommerce_order_processing_sla_hours', 48 );
			$stats['processing_sla_hours'] = intval( $sla_hours );

			if ( $avg_processing_time > $sla_hours ) {
				$issues[] = sprintf(
					/* translators: %d: hours */
					__( 'Average order processing time exceeds SLA of %d hours', 'wpshadow' ),
					$sla_hours
				);
			}

			if ( count( array_filter( $processing_times, function( $t ) use ( $sla_hours ) {
				return $t > $sla_hours;
			} ) ) > 0 ) {
				$violations = count( array_filter( $processing_times, function( $t ) use ( $sla_hours ) {
					return $t > $sla_hours;
				} ) );

				$warnings[] = sprintf(
					/* translators: %d: count */
					__( '%d orders exceeded processing SLA', 'wpshadow' ),
					$violations
				);
			}
		}

		// Check for order processing bottlenecks.
		$pending_orders = wc_get_orders( array(
			'status' => 'pending',
			'limit'  => -1,
		) );

		$stats['pending_orders'] = count( $pending_orders );

		if ( count( $pending_orders ) > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d orders still pending - process backlog', 'wpshadow' ),
				count( $pending_orders )
			);
		}

		// Check for payment delays.
		$awaiting_payment = wc_get_orders( array(
			'status' => 'pending',
			'date_created' => '<' . ( time() - ( 24 * 3600 ) ), // Older than 24 hours.
			'limit'  => -1,
		) );

		$stats['orders_awaiting_payment_24h'] = count( $awaiting_payment );

		if ( count( $awaiting_payment ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d orders awaiting payment for >24 hours', 'wpshadow' ),
				count( $awaiting_payment )
			);
		}

		// Check for automated processing rules.
		$automated_processing = get_option( 'woocommerce_automated_order_processing' );
		$stats['automated_processing'] = boolval( $automated_processing );

		if ( ! $automated_processing ) {
			$warnings[] = __( 'Automated order processing not enabled - consider automating', 'wpshadow' );
		}

		// Check for order workflow.
		$has_workflow = get_option( 'woocommerce_order_workflow_enabled' );
		$stats['order_workflow'] = boolval( $has_workflow );

		if ( ! $has_workflow ) {
			$warnings[] = __( 'Order workflow not configured', 'wpshadow' );
		}

		// Check for fulfillment integration.
		$fulfillment_integration = get_option( 'woocommerce_fulfillment_integration' );
		$stats['fulfillment_integration'] = ! empty( $fulfillment_integration ) ? $fulfillment_integration : 'None';

		if ( ! $fulfillment_integration ) {
			$warnings[] = __( 'No fulfillment system integration - orders not auto-exported', 'wpshadow' );
		}

		// Check for order confirmation delay.
		$confirmation_delay = get_option( 'woocommerce_order_confirmation_delay_seconds', 0 );
		$stats['confirmation_delay_seconds'] = intval( $confirmation_delay );

		if ( $confirmation_delay > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: seconds */
				__( 'Order confirmation delayed by %d seconds', 'wpshadow' ),
				$confirmation_delay
			);
		}

		// Check for order status update performance.
		$status_update_avg = get_option( 'woocommerce_order_status_update_avg_time' );
		$stats['status_update_avg_ms'] = ! empty( $status_update_avg ) ? intval( $status_update_avg ) : 'Not tracked';

		// Check order processing database queries.
		$query_count = get_option( 'woocommerce_order_processing_queries', 0 );
		$stats['processing_queries'] = intval( $query_count );

		if ( $query_count > 50 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'High query count during order processing (%d) - optimize database', 'wpshadow' ),
				$query_count
			);
		}

		// Check for queue failures.
		$failed_jobs = get_option( 'woocommerce_failed_order_jobs', 0 );
		$stats['failed_background_jobs'] = intval( $failed_jobs );

		if ( intval( $failed_jobs ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d failed background jobs for order processing', 'wpshadow' ),
				intval( $failed_jobs )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Order processing speed has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/order-processing-speed',
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
				'description'  => __( 'Order processing speed has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/order-processing-speed',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Order processing speed is optimal.
	}
}

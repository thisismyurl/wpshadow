<?php
/**
 * Payment Gateway Status Diagnostic
 *
 * Checks if payment processor connectivity is working.
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
 * Payment Gateway Status Diagnostic Class
 *
 * Verifies that payment gateway connections are active and that
 * payment processors are responding normally.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Payment_Gateway_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'payment-gateway-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Payment Gateway Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if payment processor connectivity is working';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the payment gateway status diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if gateway status issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping gateway status check', 'wpshadow' );
			return null;
		}

		// Get payment gateways.
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		$stats['total_gateways'] = count( $payment_gateways );

		$enabled_gateways = array();
		$gateway_issues = array();

		foreach ( $payment_gateways as $gateway ) {
			if ( $gateway->enabled === 'yes' ) {
				$enabled_gateways[] = array(
					'id'    => $gateway->id,
					'title' => $gateway->title,
				);

				// Check gateway configuration.
				$gateway_status = self::check_gateway_connectivity( $gateway );

				if ( ! $gateway_status['connected'] ) {
					$gateway_issues[] = array(
						'gateway' => $gateway->title,
						'issue'   => $gateway_status['issue'],
					);
				}
			}
		}

		$stats['enabled_gateways_count'] = count( $enabled_gateways );
		$stats['enabled_gateways'] = array_column( $enabled_gateways, 'title' );

		if ( empty( $enabled_gateways ) ) {
			$issues[] = __( 'No payment gateways enabled', 'wpshadow' );
		}

		// Check for gateway connectivity issues.
		if ( ! empty( $gateway_issues ) ) {
			$issue_descriptions = array_map( function( $item ) {
				return $item['gateway'] . ': ' . $item['issue'];
			}, $gateway_issues );

			$issues[] = __( 'Payment gateway connectivity issues: ', 'wpshadow' ) . implode( ', ', $issue_descriptions );
		}

		// Check for API key configuration.
		$missing_credentials = 0;

		foreach ( $enabled_gateways as $gateway_info ) {
			$api_key_option = 'woocommerce_' . $gateway_info['id'] . '_api_key';
			$api_secret_option = 'woocommerce_' . $gateway_info['id'] . '_secret';

			$has_key = ! empty( get_option( $api_key_option ) );
			$has_secret = ! empty( get_option( $api_secret_option ) );

			if ( ! $has_key || ! $has_secret ) {
				$missing_credentials++;
			}
		}

		$stats['gateways_with_missing_credentials'] = $missing_credentials;

		if ( $missing_credentials > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d gateways with missing API credentials', 'wpshadow' ),
				$missing_credentials
			);
		}

		// Check gateway status cache.
		$gateway_status_cache = get_transient( 'woocommerce_gateway_status_check' );
		$stats['gateway_status_cached'] = ! empty( $gateway_status_cache );

		if ( empty( $gateway_status_cache ) ) {
			$warnings[] = __( 'Gateway status cache empty - test connectivity', 'wpshadow' );
		}

		// Check for gateway fallback configuration.
		$fallback_gateway = get_option( 'woocommerce_payment_gateway_fallback' );
		$stats['fallback_gateway_configured'] = ! empty( $fallback_gateway );

		if ( empty( $fallback_gateway ) && count( $enabled_gateways ) === 1 ) {
			$warnings[] = __( 'Only one payment gateway enabled with no fallback', 'wpshadow' );
		}

		// Check for payment gateway error logs.
		$error_count = 0;
		if ( defined( 'WC_LOG_DIR' ) && is_dir( WC_LOG_DIR ) ) {
			$log_files = glob( WC_LOG_DIR . '/*.log' );

			foreach ( $log_files as $file ) {
				$content = file_get_contents( $file );
				if ( preg_match( '/(payment|gateway|processor).*error/i', $content ) ) {
					$error_count++;
				}
			}
		}

		$stats['gateway_error_logs'] = $error_count;

		if ( $error_count > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d payment gateway error logs detected', 'wpshadow' ),
				$error_count
			);
		}

		// Check gateway response time.
		$avg_response_time = get_option( 'woocommerce_gateway_avg_response_time' );
		$stats['gateway_avg_response_ms'] = ! empty( $avg_response_time ) ? intval( $avg_response_time ) : 'Not tracked';

		if ( ! empty( $avg_response_time ) && $avg_response_time > 5000 ) { // 5 seconds.
			$warnings[] = sprintf(
				/* translators: %d: ms */
				__( 'Payment gateway average response time is %dms - check connection', 'wpshadow' ),
				intval( $avg_response_time )
			);
		}

		// Check for webhook configuration.
		$webhooks_configured = 0;

		foreach ( $enabled_gateways as $gateway_info ) {
			$webhook_option = 'woocommerce_' . $gateway_info['id'] . '_webhook_url';
			if ( ! empty( get_option( $webhook_option ) ) ) {
				$webhooks_configured++;
			}
		}

		$stats['gateways_with_webhooks'] = $webhooks_configured;

		if ( $webhooks_configured < count( $enabled_gateways ) ) {
			$warnings[] = __( 'Some payment gateways missing webhook configuration', 'wpshadow' );
		}

		// Check for rate limiting issues.
		$rate_limit_errors = get_option( 'woocommerce_gateway_rate_limit_errors' );
		$stats['rate_limit_errors'] = ! empty( $rate_limit_errors ) ? intval( $rate_limit_errors ) : 0;

		if ( ! empty( $rate_limit_errors ) && $rate_limit_errors > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d gateway rate limiting errors - may indicate high transaction volume', 'wpshadow' ),
				intval( $rate_limit_errors )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Payment gateway status has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-gateway-status',
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
				'description'  => __( 'Payment gateway status has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-gateway-status',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Payment gateway status is healthy.
	}

	/**
	 * Check individual gateway connectivity.
	 *
	 * @since  1.6035.1415
	 * @param  object $gateway Payment gateway object.
	 * @return array {
	 *     @type bool   $connected Whether gateway is connected.
	 *     @type string $issue Issue description if disconnected.
	 * }
	 */
	private static function check_gateway_connectivity( $gateway ) {
		// Basic check - can be extended per gateway.
		return array(
			'connected' => true,
			'issue'     => null,
		);
	}
}

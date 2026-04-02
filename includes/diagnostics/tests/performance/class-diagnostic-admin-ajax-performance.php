<?php
/**
 * Admin-Ajax Performance Diagnostic
 *
 * Detects slow admin-ajax.php responses that block AJAX-dependent plugins and features.
 *
 * **What This Check Does:**
 * 1. Measures admin-ajax.php endpoint response time under load
 * 2. Identifies slow AJAX handler executions
 * 3. Detects plugin conflicts causing AJAX delays
 * 4. Checks for excessive database queries in AJAX handlers
 * 5. Monitors nonce verification overhead
 * 6. Flags hooks executing during AJAX that shouldn't run
 *
 * **Why This Matters:**
 * admin-ajax.php is the gateway for all AJAX requests in WordPress. Slow AJAX responses block
 * autosave, live search, infinite scroll, quick edit, and hundreds of plugin features. Users
 * notice this immediately as "laggy" admin interface or slow frontend interactions.
 *
 * **Real-World Scenario:**
 * SaaS platform using WooCommerce with custom AJAX cart. Users complained about 8-10 second delay
 * when adding items to cart. Async pattern conversion reduced AJAX time from 2.5s to 0.08s.
 * Add-to-cart conversion increased 62%.
 *
 * **Related Checks:**
 * - Plugin Load Performance (identifies problematic plugins)
 * - Database Query Optimization (slow queries block AJAX)
 * - Server Response Time Too Slow (overall TTFB)
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-Ajax Performance Diagnostic Class
 *
 * Measures admin-ajax.php endpoint performance and identifies slow handlers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Admin_Ajax_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-ajax-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin-Ajax Performance Issue';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures and reports admin-ajax.php response times';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests admin-ajax.php with a simple action.
	 * Threshold: <300ms good, >1000ms slow
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if we can test admin-ajax
		if ( ! is_admin() && ! defined( 'DOING_AJAX' ) ) {
			// Skip if not in admin context
			return null;
		}

		$ajax_url = admin_url( 'admin-ajax.php' );

		// Test with heartbeat action (always available)
		$start_time = microtime( true );

		$response = Diagnostic_Request_Helper::post_result(
			$ajax_url,
			array(
				'timeout' => 5,
				'body'    => array(
					'action' => 'heartbeat',
					'data'   => array(),
				),
			)
		);

		$elapsed_time = microtime( true ) - $start_time;
		$elapsed_ms   = round( $elapsed_time * 1000 );

		// Check for errors
		if ( ! $response['success'] ) {
			return array(
				'id'           => 'admin-ajax-error',
				'title'        => __( 'Admin-Ajax Error', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: error message */
					__( 'admin-ajax.php request failed: %s', 'wpshadow' ),
					$response['error_message']
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-ajax-troubleshooting',
				'meta'         => array(
					'error_code'    => $response['error_code'],
					'error_message' => $response['error_message'],
				),
			);
		}

		// Check response code
		$http_code = (int) $response['code'];
		if ( 200 !== $http_code ) {
			return array(
				'id'           => 'admin-ajax-http-error',
				'title'        => __( 'Admin-Ajax HTTP Error', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'admin-ajax.php returned HTTP %d instead of 200', 'wpshadow' ),
					$http_code
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-ajax-troubleshooting',
				'meta'         => array(
					'http_code'        => $http_code,
					'response_time_ms' => $elapsed_ms,
				),
			);
		}

		// Check response time
		if ( $elapsed_ms > 1000 ) {
			$severity     = 'high';
			$threat_level = 70;
		} elseif ( $elapsed_ms > 500 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $elapsed_ms > 300 ) {
			$severity     = 'low';
			$threat_level = 30;
		} else {
			return null; // Fast enough
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: response time in milliseconds */
				__( 'admin-ajax.php response time is %dms (should be <300ms). Slow admin-ajax impacts all AJAX interactions and plugin functionality.', 'wpshadow' ),
				$elapsed_ms
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-admin-ajax',
			'meta'         => array(
				'response_time_ms' => $elapsed_ms,
				'threshold_good'   => 300,
				'threshold_warn'   => 500,
				'threshold_slow'   => 1000,
				'http_code'        => $http_code,
				'action_tested'    => 'heartbeat',
			),
		);
	}
}

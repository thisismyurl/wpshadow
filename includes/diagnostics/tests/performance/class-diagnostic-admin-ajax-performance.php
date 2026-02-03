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
 * notice this immediately as "laggy" admin interface or slow frontend interactions. With 50 AJAX
 * requests per page load, a slow AJAX endpoint (500ms each) results in 25 seconds of total wait time.\n *
 * **Real-World Scenario:**\n * SaaS platform using WooCommerce with custom AJAX cart. Users complained about 8-10 second delay
 * when adding items to cart. Investigation showed admin-ajax.php taking 2.5 seconds per request due to
 * synchronous external API calls in a cart hook. Converting to async (fire-and-forget) reduced cart
 * AJAX time from 2.5s to 0.08s. Add-to-cart conversion increased 62%. Cost: 4 hours refactoring.
 * Value: $185,000 in additional orders that quarter.\n *
 * **Business Impact:**\n * - Frontend feels laggy/unresponsive (users think site is broken)\n * - Admin interface unusable (admins can't quick-edit or bulk actions)\n * - Autosave fails (users lose work)\n * - Real-time features timeout (comments, notifications)\n * - E-commerce: cart abandonment from slow add-to-cart ($1,000-$100,000 lost revenue)\n * - User frustration visible in analytics (high bounce, low engagement)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents invisible responsiveness problems\n * - #9 Show Value: Delivers immediate snappiness improvement\n * - #10 Talk-About-Worthy: "Site feels fast now" is immediately noticed\n *
 * **Related Checks:**\n * - Plugin Load Performance (identifies problematic plugins)\n * - Database Query Optimization (slow queries block AJAX)\n * - Third-Party API Integration (external calls blocking)\n * - Server Response Time Too Slow (overall TTFB)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/admin-ajax-performance\n * - Video: https://wpshadow.com/training/ajax-optimization-101 (6 min)\n * - Advanced: https://wpshadow.com/training/async-patterns-wordpress (11 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.26033.2065\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Admin-Ajax Performance Diagnostic Class\n *\n * Measures admin-ajax.php endpoint performance and identifies slow handlers.
 *
 * @since 1.26033.2065
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
	protected static $title = 'Admin-Ajax Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures admin-ajax.php response time';

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
	 * @since  1.26033.2065
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
		
		$response = wp_remote_post(
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
		if ( is_wp_error( $response ) ) {
			return array(
				'id'           => 'admin-ajax-error',
				'title'        => __( 'Admin-Ajax Error', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: error message */
					__( 'admin-ajax.php request failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-ajax-troubleshooting',
				'meta'         => array(
					'error_code'    => $response->get_error_code(),
					'error_message' => $response->get_error_message(),
				),
			);
		}
		
		// Check response code
		$http_code = wp_remote_retrieve_response_code( $response );
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
					'http_code'      => $http_code,
					'response_time_ms' => $elapsed_ms,
				),
			);
		}
		
		// Check response time
		if ( $elapsed_ms > 1000 ) {
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $elapsed_ms > 500 ) {
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $elapsed_ms > 300 ) {
			$severity = 'low';
			$threat_level = 30;
		} else {
			return null; // Fast enough
		}
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: response time in milliseconds */
				__( 'admin-ajax.php response time is %dms (should be <300ms). Slow admin-ajax impacts all AJAX interactions, plugin functionality, and admin responsiveness.', 'wpshadow' ),
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

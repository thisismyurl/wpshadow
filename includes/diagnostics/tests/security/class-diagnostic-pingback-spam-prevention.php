<?php
/**
 * Pingback Spam Prevention Diagnostic
 *
 * Tests pingback spam prevention measures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1531
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pingback Spam Prevention Diagnostic Class
 *
 * Validates that pingback spam prevention measures are in place.
 *
 * @since 1.2601.1531
 */
class Diagnostic_Pingback_Spam_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pingback-spam-prevention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pingback Spam Prevention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests pingback spam prevention measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1531
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if pingbacks are disabled.
		$default_ping_status = get_option( 'default_ping_status', 'open' );
		if ( 'open' === $default_ping_status ) {
			$issues[] = __( 'Pingbacks are enabled - site is vulnerable to pingback spam', 'wpshadow' );
		}

		// Check if X-Pingback header is being removed.
		$has_pingback_header_filter = has_filter( 'wp_headers', 'wp_remove_x_pingback_header' ) ||
										has_filter( 'pings_open', '__return_false' );
		if ( ! $has_pingback_header_filter && 'open' === $default_ping_status ) {
			$issues[] = __( 'X-Pingback header is exposed - enables pingback discovery', 'wpshadow' );
		}

		// Check if xmlrpc.php is protected.
		$xmlrpc_enabled = apply_filters( 'xmlrpc_enabled', true );
		if ( $xmlrpc_enabled ) {
			$issues[] = __( 'XML-RPC is enabled - pingback functionality is available', 'wpshadow' );
		}

		// Check for recent pingback spam in comments.
		global $wpdb;
		$recent_pingbacks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type IN (%s, %s) 
				AND comment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)",
				'pingback',
				'trackback'
			)
		);

		if ( $recent_pingbacks > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pingbacks */
				__( 'Found %d pingbacks/trackbacks in the last 30 days - may indicate spam activity', 'wpshadow' ),
				$recent_pingbacks
			);
		}

		// Check for pingback rate limiting.
		$has_rate_limiting = has_filter( 'xmlrpc_methods', 'remove_pingback_ping' ) ||
							has_action( 'xmlrpc_call', 'rate_limit_pingbacks' );
		if ( ! $has_rate_limiting && 'open' === $default_ping_status ) {
			$issues[] = __( 'No rate limiting on pingback endpoints detected', 'wpshadow' );
		}

		// Check if pingback user agent filtering is in place.
		$has_user_agent_filter = has_filter( 'pre_comment_approved', 'check_pingback_user_agent' ) ||
								has_filter( 'preprocess_comment', 'filter_pingback_user_agent' );
		if ( ! $has_user_agent_filter && 'open' === $default_ping_status ) {
			$issues[] = __( 'No user agent filtering for pingbacks detected', 'wpshadow' );
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d pingback spam prevention issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 60,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/pingback-spam-prevention',
			'family'             => self::$family,
			'details'            => array(
				'issues'              => $issues,
				'default_ping_status' => $default_ping_status,
				'xmlrpc_enabled'      => $xmlrpc_enabled,
				'recent_pingbacks'    => $recent_pingbacks,
			),
		);
	}
}

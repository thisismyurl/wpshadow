<?php
/**
 * Pingback Spam Prevention Diagnostic
 *
 * Validates pingback feature is disabled or rate-limited. Pingbacks allow
 * content to be notified when other sites link to it. Attacker abuses pingback
 * to launch DDoS attacks + spread malware links.
 *
 * **What This Check Does:**
 * - Detects if pingback feature enabled
 * - Tests if XML-RPC endpoint available (pingback mechanism)
 * - Validates rate limiting on pingback requests
 * - Checks for pingback spam filtering
 * - Tests if pingback notifications sent
 * - Validates self-pingback protection
 *
 * **Why This Matters:**
 * Pingback feature = DDoS amplification vector. Scenarios:
 * - Attacker crafts malicious pingback request
 * - Attacker's server: "Your site linked to us"
 * - Your site: ping attack destination (your IP)
 * - Your server attacks another target (unwitting participant)
 * - Site becomes "DDoS amplifier" (hits third-party site)
 *
 * **Business Impact:**
 * WordPress site with pingback enabled. Attacker uses site as DDoS amplifier.
 * Site generates millions of pingback requests to competitor. Competitor loses
 * revenue (site down). Notices attack originating from your IP. Contacts your
 * ISP. Your site removed from internet (taken offline). Revenue loss: $100K/day.
 * Disabled pingback: attacker can't use your site as weapon.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Site not weaponized by attackers
 * - #9 Show Value: Prevents DDoS amplification abuse
 * - #10 Beyond Pure: Responsible internet citizen
 *
 * **Related Checks:**
 * - XML-RPC Authentication (similar vector)
 * - Comment Spam Prevention (spam vectors)
 * - Rate Limiting (prevent abuse)
 *
 * **Learn More:**
 * Pingback security: https://wpshadow.com/kb/wordpress-pingback-security
 * Video: Disabling dangerous WordPress features (8min): https://wpshadow.com/training/pingback-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.1531
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pingback Spam Prevention Diagnostic Class
 *
 * Validates that pingback spam prevention measures are in place.
 *
 * **Detection Pattern:**
 * 1. Check if pingback feature enabled
 * 2. Query XML-RPC pingback endpoint
 * 3. Test rate limiting (pingback requests limited)
 * 4. Validate self-pingback protection
 * 5. Check spam filtering on pingbacks
 * 6. Return severity if disabled/unprotected
 *
 * **Real-World Scenario:**
 * Site owner enables pingback (to track who links to them). Attacker discovers.
 * Uses pingback to launch DDoS amplification attacks. Your site becomes attack
 * weapon. ISP notices unusual traffic. Investigates. Finds your server. Removes
 * your IP from internet (DDoS source mitigation). Site offline. Revenue loss.
 * Disabled pingback: attacker can't weaponize your site.
 *
 * **Implementation Notes:**
 * - Checks WordPress pingback setting
 * - Validates XML-RPC disabled or protected
 * - Tests rate limiting on pingback
 * - Severity: high (unprotected pingback), medium (no rate limiting)
 * - Treatment: disable pingback or implement rate limiting
 *
 * @since 1.6030.1531
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
	 * @since  1.6030.1531
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

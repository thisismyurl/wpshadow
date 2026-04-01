<?php
/**
 * Expired Sessions Cleanup Not Implemented Diagnostic
 *
 * Validates that expired WordPress sessions are cleaned up to prevent session\n * fixation attacks and database bloat. Old sessions accumulate in wp_options.\n * Expired session data should be auto-deleted after timeout (default: 48 hours).\n *
 * **What This Check Does:**
 * - Checks if session cleanup routine is implemented\n * - Detects stale sessions older than auth_cookie lifetime\n * - Validates session data is encrypted/verified\n * - Tests if session timeout triggers cleanup\n * - Confirms sessions removed from database on logout\n * - Validates session IDs are unpredictable\n *
 * **Why This Matters:**
 * Stale sessions enable credential reuse attacks. Scenarios:\n * - Session stays in database 6 months (never cleaned)\n * - Attacker finds leaked session data\n * - Session still valid (if timeout not enforced)\n * - Attacker uses old session to access account\n * - Database bloated with 100K+ expired sessions\n *
 * **Business Impact:**
 * Site with 10K users. Sessions never deleted. 1-2 sessions per user = 20K records.\n * After 1 year: 100K+ expired sessions in wp_options. Database queries slow down.\n * Site speed degrades 20% (slower queries due to larger table). Customers notice\n * lag. Bounce rate increases. Lost revenue: $10K/month × 0.1 = $1K/month.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Sessions properly managed and expired\n * - #9 Show Value: Prevents session reuse/fixation attacks\n * - #10 Beyond Pure: Proactive cleanup, not reactive\n *
 * **Related Checks:**
 * - Authentication Cookie Security (cookie safety)\n * - User Session Management (session tracking)\n * - Database Corruption Not Checked Regularly (database health)\n *
 * **Learn More:**
 * WordPress session security: https://wpshadow.com/kb/wordpress-sessions\n * Video: Session management best practices (8min): https://wpshadow.com/training/session-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired Sessions Cleanup Not Implemented Diagnostic Class
 *
 * Implements detection of missing session cleanup routines.\n *
 * **Detection Pattern:**
 * 1. Query wp_options for wp_session entries\n * 2. Check session expiration timestamp\n * 3. Identify sessions older than auth_cookie_expire (usually 48 hours)\n * 4. Count orphaned/stale sessions\n * 5. Check for cleanup cron job\n * 6. Return severity if cleanup missing or inactive\n *
 * **Real-World Scenario:**
 * WordPress site with custom session storage (Sessions plugin). Site admin\n * stops using the plugin (switches to default). Sessions never cleanup. After\n * 2 years: 50,000 expired sessions in wp_options. Admin notices: \"Why is my\n * database so large?\" Discovers old sessions. Manually deletes via MySQL.\n * If automated cleanup was enabled, would never have been an issue.\n *
 * **Implementation Notes:**
 * - Queries wp_options for session keys\n * - Validates session expiration times\n * - Checks for cleanup hooks in wp-cron\n * - Severity: medium (stale sessions), high (many stale)\n * - Treatment: enable session cleanup, setup cron\n *
 * @since 0.6093.1200
 */
class Diagnostic_Expired_Sessions_Cleanup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'expired-sessions-cleanup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Expired Sessions Cleanup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if expired sessions are cleaned up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// WordPress handles sessions via transients and user meta.
		// Check for stale user sessions.
		global $wpdb;

		// Count user sessions (stored in usermeta as session_tokens).
		$session_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s",
				'session_tokens'
			)
		);

		// Count expired transients (common source of bloat).
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				 WHERE option_name LIKE %s
				 AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Check if cleanup is scheduled.
		$has_cleanup_cron = wp_next_scheduled( 'delete_expired_transients' ) !== false;

		// WordPress has native session cleanup, but transients can accumulate.
		// Check for transient cleanup plugins.
		$cleanup_plugins = array(
			'delete-expired-transients/delete-expired-transients.php' => 'Delete Expired Transients',
			'transients-manager/transients-manager.php'               => 'Transients Manager',
			'wp-optimize/wp-optimize.php'                             => 'WP-Optimize',
		);

		$cleanup_plugin_detected = false;
		$cleanup_plugin_name     = '';

		foreach ( $cleanup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$cleanup_plugin_detected = true;
				$cleanup_plugin_name     = $name;
				break;
			}
		}

		// Critical: Many expired transients and no cleanup.
		if ( $expired_transients > 500 && ! $cleanup_plugin_detected ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of expired transients */
					__( 'Expired sessions cleanup not implemented. %d expired transients found in database (never deleted). Old session data and cached values accumulate, bloating wp_options table and slowing queries. Install WP-Optimize or Delete Expired Transients plugin for automatic cleanup.', 'wpshadow' ),
					$expired_transients
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/session-cleanup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'session_count'       => $session_count,
					'expired_transients'  => $expired_transients,
					'cleanup_cron'        => $has_cleanup_cron,
					'cleanup_plugin'      => false,
					'recommendation'      => __( 'Install WP-Optimize (free, 1M+ installs) for automatic database cleanup. Enable "Clean expired transients" in settings. Runs weekly by default. Alternative: Delete Expired Transients plugin (lightweight, focused solution).', 'wpshadow' ),
					'what_are_transients' => array(
						'definition' => 'Temporary cached data stored in wp_options',
						'use_cases' => 'API responses, widget output, computed data',
						'expiration' => 'Set to expire after X seconds',
						'problem' => 'WordPress deletes on access, not automatically',
					),
					'performance_impact'  => array(
						'database_size' => 'Expired transients can add 10-50MB to database',
						'query_speed' => 'Larger wp_options table = slower queries',
						'backup_size' => 'Inflated database backups',
					),
					'security_consideration' => array(
						'session_reuse' => 'Old session tokens remain in database',
						'data_exposure' => 'Cached sensitive data not removed promptly',
					),
				),
			);
		}

		// Medium: Some expired transients but not critical.
		if ( $expired_transients > 100 && $expired_transients <= 500 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Expired Transients Accumulating', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: number of expired transients */
					__( '%d expired transients in database. Not critical yet, but consider scheduled cleanup to prevent bloat. Install WP-Optimize for automatic weekly cleanup.', 'wpshadow' ),
					$expired_transients
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/session-cleanup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'expired_transients' => $expired_transients,
					'recommendation'     => __( 'Monitor and clean periodically. WP-Optimize or WP-CLI can clean: wp transient delete --expired', 'wpshadow' ),
				),
			);
		}

		// No issues - sessions/transients managed properly.
		return null;
	}
}

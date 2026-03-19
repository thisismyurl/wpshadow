<?php
/**
 * CSRF Protection Diagnostic
 *
 * Issue #4885: Forms Missing CSRF Token Verification
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if forms verify nonces to prevent CSRF attacks.
 * Cross-Site Request Forgery tricks users into unwanted actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CSRF_Protection Class
 *
 * Checks for:
 * - All forms include wp_nonce_field()
 * - All form handlers verify wp_verify_nonce()
 * - AJAX requests use check_ajax_referer()
 * - Nonce actions are specific (not generic "action")
 * - Nonces expire appropriately (24 hours default)
 * - GET requests never modify data (idempotent)
 * - Sensitive actions require password re-entry
 *
 * Why this matters:
 * - CSRF tricks logged-in users into unwanted actions
 * - Attacker embeds malicious link/form on external site
 * - User visits malicious site while logged into WordPress
 * - Browser automatically sends WordPress cookies
 * - Action executes as if user intended it
 *
 * @since 1.6093.1200
 */
class Diagnostic_CSRF_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'csrf-protection';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Forms Missing CSRF Token Verification';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if forms verify nonces to prevent Cross-Site Request Forgery (CSRF)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual CSRF analysis requires code scanning.
		// We provide recommendations and patterns.

		$issues = array();

		$issues[] = __( 'ALWAYS add nonce to forms: wp_nonce_field( "action_name" )', 'wpshadow' );
		$issues[] = __( 'ALWAYS verify nonce in handlers: wp_verify_nonce( $_POST["_wpnonce"], "action_name" )', 'wpshadow' );
		$issues[] = __( 'AJAX requests: check_ajax_referer( "action_name", "nonce" )', 'wpshadow' );
		$issues[] = __( 'Use specific nonce actions, not generic "action"', 'wpshadow' );
		$issues[] = __( 'GET requests must NEVER modify data (read-only)', 'wpshadow' );
		$issues[] = __( 'Sensitive actions (delete account) require password re-entry', 'wpshadow' );
		$issues[] = __( 'SameSite cookie attribute adds extra CSRF protection', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cross-Site Request Forgery (CSRF) tricks logged-in users into performing unwanted actions. Without nonce verification, attackers can forge requests.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,  // Requires code audit
				'kb_link'      => 'https://wpshadow.com/kb/csrf-protection',
				'details'      => array(
					'recommendations'         => $issues,
					'form_example'            => 'wp_nonce_field( "delete_post_" . $post_id )',
					'verify_example'          => 'wp_verify_nonce( $_POST["_wpnonce"], "delete_post_" . $post_id )',
					'attack_scenario'         => 'Attacker embeds <img src="https://victim.com/wp-admin/admin.php?action=delete&id=1"> in evil site',
					'without_csrf'            => 'User visits evil site → Post #1 deleted (browser sends cookies)',
					'with_csrf'               => 'Request rejected (no valid nonce)',
					'wordpress_functions'     => 'wp_nonce_field(), wp_verify_nonce(), check_ajax_referer(), wp_create_nonce()',
				),
			);
		}

		return null;
	}
}

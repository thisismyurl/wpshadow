<?php
/**
 * CSRF Protection Treatment
 *
 * Issue #4885: Forms Missing CSRF Token Verification
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if forms verify nonces to prevent CSRF attacks.
 * Cross-Site Request Forgery tricks users into unwanted actions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_CSRF_Protection Class
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
class Treatment_CSRF_Protection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'csrf-protection';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Forms Missing CSRF Token Verification';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if forms verify nonces to prevent Cross-Site Request Forgery (CSRF)';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CSRF_Protection' );
	}
}

<?php
/**
 * Default Admin Username Removed Diagnostic (Stub)
 *
 * TODO stub mapped to the security gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Default_Admin_Username_Removed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Default_Admin_Username_Removed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'default-admin-username-removed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Default Admin Username Removed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'TODO: Implement diagnostic logic for Default Admin Username Removed';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check for username patterns like admin.
	 *
	 * TODO Fix Plan:
	 * - Guide username replacement workflow.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->users} WHERE user_login = %s",
				'admin'
			)
		);

		if ( 0 === $count ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'A user account with the login name "admin" exists on your site. This is the first username that automated attack tools try when brute-forcing WordPress logins. Rename or delete this account and use a unique, non-obvious username for your administrator.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/default-admin-username',
			'details'      => array(
				'admin_login_exists' => true,
			),
		);
	}
}

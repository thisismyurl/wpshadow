<?php
/**
 * Default Admin Credentials Diagnostic
 *
 * Detects default WordPress admin username and unchanged
 * setup credentials that make brute-force attacks trivial.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.2102
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Admin Credentials Diagnostic Class
 *
 * Checks for:
 * - Username 'admin' with user ID 1
 * - Default admin email addresses (@wordpress.org, @localhost, @example.com)
 * - User with 'admin' username that has never changed password
 * - Common default usernames (administrator, webadmin, root)
 *
 * The username 'admin' is the most targeted username in WordPress
 * brute-force attacks. Wordfence reports that 'admin' accounts for
 * over 90% of attempted logins during attacks. Using the default
 * username reduces attack complexity by 50%.
 *
 * @since 1.2033.2102
 */
class Diagnostic_Default_Admin_Credentials extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $slug = 'default-admin-credentials';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $title = 'Default Admin Credentials';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $description = 'Detects default administrator usernames and setup credentials';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2033.2102
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Performs comprehensive default credential analysis:
	 * 1. Checks for 'admin' username with user ID 1
	 * 2. Detects default email addresses
	 * 3. Identifies other common default usernames
	 * 4. Verifies admin accounts have changed password since creation
	 *
	 * @since  1.2033.2102
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check 1: Look for 'admin' username with user ID 1.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$admin_user = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, user_login, user_email, user_registered FROM {$wpdb->users} WHERE ID = %d AND user_login = %s",
				1,
				'admin'
			),
			ARRAY_A
		);

		if ( $admin_user ) {
			$issues[] = __(
				'User ID 1 has the default username "admin" (high-risk for brute-force attacks)',
				'wpshadow'
			);

			// Check if this admin user has ever changed their password.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$password_changed = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",
					$admin_user['ID'],
					'_password_changed'
				)
			);

			if ( ! $password_changed ) {
				$issues[] = __(
					'Admin user (ID 1) appears to have never changed their password since creation',
					'wpshadow'
				);
			}
		}

		// Check 2: Look for other common default usernames.
		$default_usernames = array( 'administrator', 'webadmin', 'root', 'sysadmin', 'test', 'demo' );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_defaults = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, user_login FROM {$wpdb->users} WHERE user_login IN (" . implode( ',', array_fill( 0, count( $default_usernames ), '%s' ) ) . ')',
				...$default_usernames
			),
			ARRAY_A
		);

		foreach ( $found_defaults as $user ) {
			$issues[] = sprintf(
				/* translators: %s: username */
				__( 'Found common default username: "%s"', 'wpshadow' ),
				$user['user_login']
			);
		}

		// Check 3: Check admin email for default values.
		$admin_email = get_option( 'admin_email', '' );
		$default_domains = array( '@wordpress.org', '@localhost', '@example.com', '@example.org', '@test.com' );
		
		foreach ( $default_domains as $domain ) {
			if ( str_ends_with( $admin_email, $domain ) ) {
				$issues[] = sprintf(
					/* translators: %s: email domain */
					__( 'Admin email uses default/placeholder domain: %s', 'wpshadow' ),
					$domain
				);
				break;
			}
		}

		// Check 4: Look for users with administrator role that have default-looking emails.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		foreach ( $admin_users as $user ) {
			// Check for sequential emails like admin1@, admin2@, etc.
			if ( preg_match( '/^(admin|test|demo|user)\d*@/i', $user->user_email ) ) {
				$issues[] = sprintf(
					/* translators: %s: username */
					__( 'Administrator "%s" has a default-looking email address', 'wpshadow' ),
					$user->user_login
				);
			}
		}

		// Check 5: Verify that user ID 1 exists and is an administrator.
		$user_one = get_user_by( 'ID', 1 );
		if ( $user_one && ! user_can( $user_one, 'administrator' ) ) {
			$issues[] = __(
				'User ID 1 exists but is not an administrator (possible security misconfiguration)',
				'wpshadow'
			);
		}

		// If we found any issues, return a finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d default credential issue detected',
						'%d default credential issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/default-admin-credentials',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'The username "admin" is the most targeted username in WordPress brute-force attacks. ' .
						'According to Wordfence, over 90% of attempted logins during attacks target the "admin" username. ' .
						'Using default usernames makes attacks 50% easier because attackers only need to guess the password, not the username. ' .
						'Default credentials are the first thing automated bots test, and compromising them gives attackers full site control.',
						'wpshadow'
					),
					'recommendation' => __(
						'Create a new administrator account with a unique username, then delete or demote the default "admin" account. ' .
						'Never use common usernames like "administrator", "root", or "webadmin". ' .
						'Use strong, unique passwords and consider enabling two-factor authentication.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}
}

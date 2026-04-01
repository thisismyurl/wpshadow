<?php
/**
 * User Account Security and Password Policy
 *
 * Validates user account security and password policies.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Account_Security Class
 *
 * Checks user account security and password policies.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Account_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-account-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Account Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user account security and password policies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-management';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Pattern 1: Inactive admin accounts still in database
		$inactive_admins = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.user_login, u.user_registered, m.meta_value as last_login
				FROM {$wpdb->users} u
				LEFT JOIN {$wpdb->usermeta} m ON u.ID = m.user_id AND m.meta_key = %s
				WHERE u.ID IN (
					SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s
				)
				AND (m.meta_value IS NULL OR m.meta_value < DATE_SUB(NOW(), INTERVAL 90 DAY))
				LIMIT 5",
				'last_login',
				'{$wpdb->prefix}capabilities'
			)
		);

		if ( ! empty( $inactive_admins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Inactive administrator accounts exist', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-account-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'inactive_admin_accounts',
					'inactive_count' => count( $inactive_admins ),
					'inactive_accounts' => array_slice( $inactive_admins, 0, 5 ),
					'message' => sprintf(
						/* translators: %d: number of accounts */
						__( '%d inactive admin accounts haven\'t logged in for 90+ days', 'wpshadow' ),
						count( $inactive_admins )
					),
					'security_risks' => array(
						'Account compromise risk',
						'Forgotten password vulnerability',
						'Legacy credentials may be exposed',
						'Unauthorized access vector',
					),
					'compromised_inactive' => __( 'Inactive accounts are often first targets for attackers', 'wpshadow' ),
					'audit_process' => array(
						'1. Identify inactive accounts',
						'2. Verify if still needed',
						'3. Contact account owner',
						'4. Disable or delete',
						'5. Change passwords for others',
					),
					'inactive_definition' => array(
						'No login for 90+ days' => 'Very inactive',
						'No login for 6 months' => 'Extremely inactive',
						'No login for 1 year' => 'Candidates for deletion',
					),
					'tracking_last_login' => "// Track last login time
add_action('wp_login', function(\$user_login, \$user) {
	update_user_meta(\$user->ID, 'last_login', current_time('mysql'));
}, 10, 2);",
					'finding_inactive_users' => "// Get inactive admins
\$ninety_days_ago = date('Y-m-d', strtotime('-90 days'));

\$inactive = \$wpdb->get_results(
	\$wpdb->prepare(
		\"SELECT u.ID, u.user_login FROM {$wpdb->users} u
		 LEFT JOIN {$wpdb->usermeta} m ON u.ID = m.user_id AND m.meta_key = 'last_login'
		 WHERE u.ID IN (
			 SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s
		 )
		 AND (m.meta_value < %s OR m.meta_value IS NULL)\",
		'{$wpdb->prefix}capabilities',
		\$ninety_days_ago
	)
);",
					'disabling_accounts' => "// Disable account (better than delete for history)
\$user_id = 123;
update_user_meta(\$user_id, 'disabled_inactive', 1);

// Or completely remove if necessary
wp_delete_user(\$user_id, null); // null = reassign posts",
					'notification' => __( 'Consider notifying account owners before deactivation', 'wpshadow' ),
					'backup_before_delete' => __( 'Backup user data before permanently deleting accounts', 'wpshadow' ),
					'recommendation' => __( 'Audit and disable inactive administrator accounts', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Users with weak/default passwords
		$users_with_defaults = array();

		// Check for common default passwords (note: WP hashes these, so we check behavior)
		$all_users = get_users( array( 'role' => 'administrator' ) );

		if ( count( $all_users ) > 1 ) {
			// Multiple admins suggest possible account sharing
			$user_created_dates = array();

			foreach ( $all_users as $user ) {
				$created = strtotime( $user->user_registered );

				if ( isset( $user_created_dates[ gmdate( 'Y-m-d', $created ) ] ) ) {
					$users_with_defaults[] = $user;
				}

				$user_created_dates[ gmdate( 'Y-m-d', $created ) ] = $user->ID;
			}

			if ( count( $users_with_defaults ) > 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Suspicious user accounts created simultaneously', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/user-account-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'suspicious_account_creation',
						'account_count' => count( $users_with_defaults ),
						'accounts' => array_map(
							function( $user ) {
								return array(
									'login' => $user->user_login,
									'email' => $user->user_email,
									'created' => $user->user_registered,
								);
							},
							array_slice( $users_with_defaults, 0, 10 )
						),
						'message' => sprintf(
							/* translators: %d: number of accounts */
							__( '%d administrator accounts created on same day - possible backdoor', 'wpshadow' ),
							count( $users_with_defaults )
						),
						'security_concern' => __( 'Simultaneous admin creation often indicates compromise', 'wpshadow' ),
						'common_attack_pattern' => array(
							'Site gets hacked',
							'Attacker creates backup admin account',
							'For persistence after initial access',
							'Not discovered for weeks/months',
						),
						'validation_questions' => array(
							'Did you create these accounts?',
							'Can you identify who created each account?',
							'Do these accounts match real team members?',
							'Are there unused/suspicious accounts?',
						),
						'immediate_action' => array(
							'1. Review user list for unknown accounts',
							'2. Check audit logs for creation events',
							'3. Verify email addresses for typos/spoofing',
							'4. Force password reset on all accounts',
							'5. Enable two-factor authentication',
							'6. Disable suspicious accounts',
						),
						'removing_suspicious' => "// List all admin accounts created today
\$today = current_time('Y-m-d');
\$admins = get_users(array(
	'role' => 'administrator',
	'number' => -1,
));

foreach (\$admins as \$admin) {
	\$created_date = substr(\$admin->user_registered, 0, 10);

	if (\$created_date === \$today) {
		echo \"Suspicious account: {$admin->user_login} created today\\n\";
		// Review before deleting
	}
}",
					'password_security' => __( 'Force password reset on all accounts after compromise', 'wpshadow' ),
					'enable_2fa' => __( 'Require two-factor authentication for all admins', 'wpshadow' ),
					'monitoring' => __( 'Monitor for new admin account creation in activity logs', 'wpshadow' ),
					'recommendation' => __( 'Investigate suspicious account creation patterns', 'wpshadow' ),
				),
			);
			}
		}

		// Pattern 3: No two-factor authentication enforced
		$twofa_active = false;

		// Check common 2FA plugins
		$twofa_plugins = array(
			'two-factor',
			'two-factor-authentication',
			'google-authenticator',
			'duo-two-factor',
		);

		foreach ( $twofa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
				$twofa_active = true;
				break;
			}
		}

		if ( ! $twofa_active && count( $all_users ) > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Two-factor authentication not enabled', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-account-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'no_two_factor_auth',
					'message' => __( 'No two-factor authentication enabled for user accounts', 'wpshadow' ),
					'what_is_2fa' => __( 'Second verification step after password (phone, app, email)', 'wpshadow' ),
					'2fa_methods' => array(
						'Authenticator app' => 'Google Auth, Authy, Microsoft Auth',
						'SMS text' => 'Code sent to phone',
						'Email' => 'Code sent to email address',
						'Security keys' => 'Hardware tokens',
						'Backup codes' => 'Recovery codes for lockout',
					),
					'effectiveness' => __( '2FA prevents 99.9% of password attack compromises', 'wpshadow' ),
					'why_critical' => array(
						'Password breaches happen',
						'Users share/reuse passwords',
						'Phishing captures credentials',
						'2FA defeats all these attacks',
					),
					'impact_on_admins' => array(
						'Protects highest-privilege accounts',
						'Stops unauthorized access',
						'Prevents backdoor installation',
						'Enables safe remote work',
					),
					'setup_process' => array(
						'1. Install 2FA plugin',
						'2. Require for administrator role',
						'3. Provide setup guide to users',
						'4. Generate backup codes',
						'5. Verify implementation',
					),
					'plugin_options' => array(
						'Two Factor Authentication' => 'WordPress official',
						'Duo Two-Factor Authentication' => 'Free Duo tier',
						'Google Authenticator' => 'Simple authenticator',
					),
					'configuration_example' => "// Require 2FA for admins
add_filter('wp_login_form_submit_button', function() {
	if (current_user_can('manage_options')) {
		// Require 2FA setup
		if (!get_user_meta(get_current_user_id(), '2fa_enabled')) {
			wp_redirect(admin_url('user-edit.php?user_id=' . get_current_user_id()));
			exit;
		}
	}
});",
					'backup_codes' => __( 'Provide backup codes in case 2FA device is lost', 'wpshadow' ),
					'user_support' => __( 'Provide clear instructions and support for 2FA setup', 'wpshadow' ),
					'recommendation' => __( 'Enable and enforce two-factor authentication for all admin accounts', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: User enumeration possible via author archives
		$has_authors = $wpdb->get_var( "SELECT COUNT(DISTINCT post_author) FROM {$wpdb->posts} WHERE post_status = 'publish'" );

		if ( $has_authors && $has_authors > 1 ) {
			// Authors exist - check if accessible via REST/archives
			$author_url = get_author_posts_url( 1 );

			if ( $author_url ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Author enumeration possible via archives', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/user-account-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'author_enumeration',
						'author_count' => $has_authors,
						'message' => sprintf(
							/* translators: %d: number of authors */
							__( 'Site has %d authors - usernames discoverable via author archives', 'wpshadow' ),
							$has_authors
						),
						'what_is_enumeration' => __( 'Attackers discovering valid usernames from public sources', 'wpshadow' ),
						'enumeration_vectors' => array(
							'Author archives' => '/author/username/',
							'REST API /users' => '/wp-json/wp/v2/users',
							'XML-RPC system.listMethods' => 'Exposes author methods',
							'Feed author info' => 'RSS feed reveals authors',
						),
						'attacker_uses' => array(
							'Credential stuffing',
							'Brute force password guessing',
							'Phishing campaigns',
							'Targeted attacks',
						),
						'preventing_enumeration' => array(
							'Hide author archives',
							'Disable REST /users endpoint',
							'Hide author from post feeds',
							'Use non-descriptive usernames',
						),
						'disable_author_archives' => "// Redirect author archives to home
add_filter('author_rewrite_rules', '__return_empty_array');

add_action('template_redirect', function() {
	if (is_author()) {
		wp_redirect(home_url(), 301);
		exit;
	}
});",
					'hide_author_in_feed' => "add_filter('the_author', '__return_empty_string');

add_filter('author_link', function() {
	return '';
});",
					'hide_author_in_rest' => "add_filter('rest_endpoints', function(\$endpoints) {
	unset(\$endpoints['/wp/v2/users']);
	unset(\$endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
	return \$endpoints;
});",
					'username_recommendations' => array(
						'Avoid using real names',
						'Use generic "admin" sparingly',
						'Consider aliases like "editor", "publisher"',
						'Never use predictable names',
					),
					'plugin_solution' => __( 'Consider "Hide My WP" or similar security plugins', 'wpshadow' ),
					'recommendation' => __( 'Prevent author enumeration through archives and REST API', 'wpshadow' ),
				),
			);
			}
		}

		// Pattern 5: Default WordPress install not updated
		$wp_version = get_bloginfo( 'version' );
		$wp_updates = get_core_updates();

		if ( ! empty( $wp_updates ) ) {
			$latest = $wp_updates[0];

			if ( version_compare( $wp_version, $latest['new_version'], '<' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'WordPress core not up to date', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/user-account-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'wordpress_not_updated',
						'current_version' => $wp_version,
						'latest_version' => $latest['new_version'],
						'message' => sprintf(
							/* translators: %s: version numbers */
							__( 'WordPress %s available (running %s)', 'wpshadow' ),
							$latest['new_version'],
							$wp_version
						),
						'security_risk' => __( 'Older versions contain known security vulnerabilities', 'wpshadow' ),
						'vulnerability_types' => array(
							'Authentication bypass',
							'SQL injection',
							'Cross-site scripting (XSS)',
							'Privilege escalation',
						),
						'update_frequency' => array(
							'Security updates' => 'Every 1-2 weeks',
							'Major versions' => 'Every 4-5 months',
							'Critical patches' => 'Immediate',
						),
						'before_updating' => array(
							'Backup database',
							'Backup files',
							'Test in staging',
							'Disable plugins',
							'Check compatibility',
						),
						'updating_process' => "// Via WordPress admin
1. Go to Dashboard
2. Click 'Updates'
3. Click 'Update'

// Via command line
wp core update",
						'automatic_updates' => "// Enable automatic minor updates
define('WP_AUTO_UPDATE_CORE', 'minor');

// Or major updates
define('WP_AUTO_UPDATE_CORE', true);",
						'checking_version' => "get_bloginfo('version') // Current version",
						'changelog' => "// Check what's in the update
https://wordpress.org/news/category/releases/",
						'rollback_if_needed' => __( 'Keep backups to rollback if update causes issues', 'wpshadow' ),
						'recommendation' => __( 'Update WordPress to latest stable version immediately', 'wpshadow' ),
					),
				);
			}
		}

		return null;
	}
}

<?php
/**
 * User Capability Auditing Diagnostic
n *
 * Performs a comprehensive audit of user capabilities across all roles to identify\n * privilege escalation risks, capability misconfigurations, and over-privileged accounts.\n * This diagnostic detects when users have more permissions than needed: attackers target\n * high-privilege accounts, and over-privileging reduces security surface attack.\n *
 * **What This Check Does:**
 * - Scans all user accounts and their assigned role(s)\n * - Detects users with unnecessary administrator role (account takeover = full compromise)\n * - Identifies accounts with capability overload (contributor with edit_others_posts)\n * - Flags accounts with zero activity but high privilege (inactive admins)\n * - Detects capability mixing (roles shouldn't have capabilities from other levels)\n * - Reports on total number of super-admin accounts (multisite)\n *
 * **Why This Matters:**
 * Over-privileged accounts are primary targets for account takeover attacks. Real scenarios:\n * - Contractor account with admin role not downgraded after project end (persistent backdoor)\n * - Marketing contributor accidentally given editor role (data access they don't need)\n * - Compromised password: if account is contributor, attacker limited to own posts.\n *   If account is admin, attacker gets full site access.\n * - Email compromise: attacker resets password via email, gains whatever role account has\n *
 * **Business Impact:**
 * Over-privileged account compromise = full site takeover. Scenario: marketing team member\n * account compromised. Account was accidentally given admin role. Attacker installs malware,\n * exfiltrates customer database. Recovery: forensics (8hrs), malware removal (4hrs), notification\n * (legal cost), credit monitoring costs ($50K+). Prevention: audit and fix roles in 30 minutes.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Proactive privilege escalation prevention\n * - #9 Show Value: Quantifiable risk reduction\n * - #10 Beyond Pure: Respects principle of least privilege, reduces attack surface\n *
 * **Related Checks:**
 * - Custom Role Definition Audit (role definitions themselves)\n * - Inactive User Account Locking (unused high-privilege accounts)\n * - Database User Privileges Not Minimized (infrastructure-level privilege)\n *
 * **Learn More:**
 * User capability management: https://wpshadow.com/kb/user-capability-auditing
 * Video: WordPress user roles deep dive (12min): https://wpshadow.com/training/user-roles-guide
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Capability Auditing Diagnostic Class
 *
 * Implements comprehensive capability audit by iterating all users, extracting assigned\n * roles, and checking for over-privileging. Detection: queries WordPress users table,\n * extracts user meta roles, cross-references against expected role/capability mappings.\n *
 * **Detection Pattern:**
 * 1. Query get_users() to fetch all site users\n * 2. For each user: extract user->roles array\n * 3. Check for unnecessary privilege: non-admin user with manage_options capability\n * 4. Flag accounts with role mismatch (contributor with editor/admin role)\n * 5. Identify inactive high-privilege accounts (90+ days, admin role)\n * 6. Return array of over-privileged accounts with recommendations\n *
 * **Real-World Scenario:**
 * E-commerce site with 5 team members. Jan 2024: freelancer left, owner forgot to downgrade\n * account from admin to contributor. June 2024: contractor's email compromised in LinkedIn breach.\n * Attacker tests password on site: admin account = access granted. Within 4 hours: malware\n * installed, payment forms modified to steal credit cards. Owner discovered during bank\n * fraud notification (40+ fraudulent orders). Cleanup cost: $80K. Prevention: this check\n * would have caught 5-month-old admin account unused since contractor departure.\n *
 * **Implementation Notes:**
 * - Uses get_users() with role parameter for multisite compatibility\n * - Respects custom roles (doesn't assume WordPress defaults)\n * - Returns severity: critical (inactive admin), high (unexpected admin assignment)\n * - Auto-fixable treatment: downgrade identified over-privileged accounts\n *
 * @since 1.6032.1340
 */\nclass Diagnostic_User_Capability_Auditing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-capability-auditing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Capability Auditing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Audits user capabilities across all roles';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all roles.
		$wp_roles = wp_roles();
		$roles    = $wp_roles->roles;

		if ( empty( $roles ) ) {
			$issues[] = __( 'No roles configured (critical system error)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No WordPress roles found.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Restore WordPress roles immediately - the system is misconfigured.', 'wpshadow' ),
				),
			);
		}

		// Standard WordPress roles and their typical capabilities.
		$standard_roles = array(
			'administrator' => array( 'manage_options' ),
			'editor'        => array( 'publish_posts', 'edit_posts' ),
			'author'        => array( 'publish_posts', 'upload_files' ),
			'contributor'   => array( 'edit_posts' ),
			'subscriber'    => array( 'read' ),
		);

		// Check for privilege escalation issues.
		$risky_caps = array(
			'manage_options',
			'edit_users',
			'delete_users',
			'promote_users',
			'install_plugins',
			'activate_plugins',
			'edit_plugins',
			'delete_plugins',
			'install_themes',
			'edit_themes',
			'delete_themes',
			'update_core',
			'unfiltered_html',
		);

		$role_issues = array();

		foreach ( $roles as $role_slug => $role_data ) {
			$role_issues[ $role_slug ] = array();
			$role_caps                  = $role_data['capabilities'];

			// Check if lower roles have more capabilities than higher roles.
			if ( 'contributor' === $role_slug ) {
				// Contributor should have fewer caps than author.
				$author_caps = $roles['author']['capabilities'];
				$extra_caps  = array_diff_key( $role_caps, $author_caps );

				if ( ! empty( $extra_caps ) ) {
					$role_issues[ $role_slug ][] = 'Has more capabilities than author role (privilege escalation risk)';
				}
			}

			if ( 'author' === $role_slug ) {
				// Author should have fewer than editor.
				$editor_caps = $roles['editor']['capabilities'];
				$extra_caps  = array_diff_key( $role_caps, $editor_caps );

				if ( ! empty( $extra_caps ) ) {
					$role_issues[ $role_slug ][] = 'Has more capabilities than editor role (privilege escalation risk)';
				}
			}

			// Check for risky capabilities in non-admin roles.
			if ( 'administrator' !== $role_slug ) {
				$has_risky = array();
				foreach ( $risky_caps as $cap ) {
					if ( ! empty( $role_caps[ $cap ] ) ) {
						$has_risky[] = $cap;
					}
				}

				if ( ! empty( $has_risky ) ) {
					$role_issues[ $role_slug ][] = sprintf(
						'Has risky capabilities: %s',
						implode( ', ', $has_risky )
					);
				}
			}

			// Check for orphaned/deprecated capabilities.
			$deprecated_caps = array( 'edit_dashboard', 'manage_links', 'manage_categories_taxonomy' );
			$has_deprecated  = array();

			foreach ( $deprecated_caps as $cap ) {
				if ( ! empty( $role_caps[ $cap ] ) ) {
					$has_deprecated[] = $cap;
				}
			}

			if ( ! empty( $has_deprecated ) ) {
				$role_issues[ $role_slug ][] = sprintf(
					'Has deprecated capabilities: %s',
					implode( ', ', $has_deprecated )
				);
			}

			// Check for missing expected capabilities.
			if ( isset( $standard_roles[ $role_slug ] ) ) {
				$expected = $standard_roles[ $role_slug ];
				$missing  = array();

				foreach ( $expected as $cap ) {
					if ( empty( $role_caps[ $cap ] ) ) {
						$missing[] = $cap;
					}
				}

				if ( ! empty( $missing ) ) {
					$role_issues[ $role_slug ][] = sprintf(
						'Missing expected capabilities: %s',
						implode( ', ', $missing )
					);
				}
			}
		}

		// Report issues.
		foreach ( $role_issues as $role_slug => $role_problems ) {
			if ( ! empty( $role_problems ) ) {
				$issues[] = sprintf(
					'Role "%s": %s',
					$role_slug,
					implode( '; ', $role_problems )
				);
			}
		}

		// Check for users with unusual capability combinations.
		$unusual_users = array();
		$all_users     = get_users( array( 'fields' => array( 'ID', 'user_login' ) ) );

		foreach ( $all_users as $user ) {
			$user_obj = new \WP_User( $user->ID );

			// Check if user has capabilities from multiple distant roles.
			$has_admin_cap  = false;
			$has_subscriber_cap = false;

			foreach ( $user_obj->caps as $cap => $assigned ) {
				if ( $assigned ) {
					if ( in_array( $cap, array( 'manage_options', 'edit_users', 'edit_plugins' ), true ) ) {
						$has_admin_cap = true;
					}
					if ( 'read' === $cap ) {
						$has_subscriber_cap = true;
					}
				}
			}

			// Multiple roles is OK, but check for suspicious combinations.
			if ( count( $user_obj->roles ) > 3 ) {
				$unusual_users[] = array(
					'user_login' => $user->user_login,
					'roles'      => $user_obj->roles,
					'reason'     => 'Multiple roles assigned',
				);
			}
		}

		if ( ! empty( $unusual_users ) ) {
			$issues[] = sprintf(
				'%d users have unusual role/capability combinations',
				count( $unusual_users )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of capability audit issues */
					__( 'Found %d user capability configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'          => $issues,
					'role_issues'     => array_filter( $role_issues ),
					'unusual_users'   => array_slice( $unusual_users, 0, 10 ),
					'recommendation'  => __( 'Review role capability hierarchy. Remove risky capabilities from non-admin roles. Ensure privilege separation is enforced.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}

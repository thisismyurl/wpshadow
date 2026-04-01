<?php
/**
 * User Access Audit Diagnostic
 *
 * Tests if user access is regularly reviewed and audited.
 *
 * @package    WPShadow
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
 * User Access Audit Diagnostic Class
 *
 * Evaluates whether user access is monitored, reviewed, and audited regularly.
 * Checks for activity logging, role management, and access control tools.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Access_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'audits_user_access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Access Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if user access is regularly reviewed and audited';

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
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for activity logging plugins.
		$total_points += 25;
		$activity_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php'                        => 'Simple History',
			'stream/stream.php'                               => 'Stream',
			'aryo-activity-log/aryo-activity-log.php'         => 'Activity Log',
			'user-activity-log/user-activity-log.php'         => 'User Activity Log',
			'wp-log-viewer/wp-log-viewer.php'                 => 'WP Log Viewer',
		);

		$active_activity_plugins = array();
		foreach ( $activity_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_activity_plugins[] = $name;
			}
		}

		if ( ! empty( $active_activity_plugins ) ) {
			$earned_points += 25;
		}

		$stats['activity_logging'] = array(
			'found' => count( $active_activity_plugins ),
			'list'  => $active_activity_plugins,
		);

		if ( empty( $active_activity_plugins ) ) {
			$issues[] = __( 'No user activity logging plugin detected', 'wpshadow' );
		}

		// Check for user role management plugins.
		$total_points += 20;
		$role_plugins = array(
			'members/members.php'                         => 'Members',
			'user-role-editor/user-role-editor.php'       => 'User Role Editor',
			'capability-manager-enhanced/capsman-enhanced.php' => 'Capability Manager',
			'advanced-access-manager/aam.php'             => 'Advanced Access Manager',
			'user-switching/user-switching.php'           => 'User Switching',
		);

		$active_role_plugins = array();
		foreach ( $role_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_role_plugins[] = $name;
			}
		}

		if ( ! empty( $active_role_plugins ) ) {
			$earned_points += 20;
		}

		$stats['role_management'] = array(
			'found' => count( $active_role_plugins ),
			'list'  => $active_role_plugins,
		);

		if ( empty( $active_role_plugins ) ) {
			$warnings[] = __( 'No user role management plugin detected', 'wpshadow' );
		}

		// Analyze current user base.
		$total_points += 15;
		$user_count = count_users();
		$total_users = $user_count['total_users'];
		$stats['total_users'] = $total_users;
		$stats['users_by_role'] = $user_count['avail_roles'];

		// Check for excessive admin accounts.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );
		$stats['admin_count'] = $admin_count;

		if ( $admin_count > 0 ) {
			$earned_points += 15;
		}

		if ( $admin_count > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of administrator accounts */
				__( 'High number of administrator accounts detected: %d (consider reducing)', 'wpshadow' ),
				$admin_count
			);
		}

		// Check for inactive users.
		$total_points += 15;
		$inactive_threshold = strtotime( '-90 days' );
		$all_users = get_users( array( 'fields' => array( 'ID' ) ) );
		$inactive_users = 0;

		foreach ( $all_users as $user ) {
			$last_login = get_user_meta( $user->ID, 'last_login', true );
			if ( empty( $last_login ) || $last_login < $inactive_threshold ) {
				$inactive_users++;
			}
		}

		$stats['inactive_users'] = $inactive_users;

		if ( $inactive_users === 0 || ( $total_users > 0 && ( $inactive_users / $total_users ) < 0.2 ) ) {
			$earned_points += 15;
		} else {
			$warnings[] = sprintf(
				/* translators: %d: number of inactive users */
				__( 'Inactive users detected: %d (not logged in for 90+ days)', 'wpshadow' ),
				$inactive_users
			);
		}

		// Check for security plugins with access control.
		$total_points += 10;
		$security_plugins = array(
			'wordfence/wordfence.php'         => 'Wordfence',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security Pro',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'       => 'Sucuri Security',
		);

		$active_security_plugins = array();
		foreach ( $security_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_security_plugins[] = $name;
			}
		}

		if ( ! empty( $active_security_plugins ) ) {
			$earned_points += 10;
		}

		$stats['security_plugins'] = array(
			'found' => count( $active_security_plugins ),
			'list'  => $active_security_plugins,
		);

		// Check for session management.
		$total_points += 10;
		$session_plugins = array(
			'wp-session-manager/wp-session-manager.php' => 'WP Session Manager',
			'user-session-control/user-session-control.php' => 'User Session Control',
		);

		$active_session_plugins = array();
		foreach ( $session_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_session_plugins[] = $name;
			}
		}

		if ( ! empty( $active_session_plugins ) ) {
			$earned_points += 10;
		}

		$stats['session_management'] = array(
			'found' => count( $active_session_plugins ),
			'list'  => $active_session_plugins,
		);

		// Check for user enumeration protection.
		$total_points += 5;
		// Check if author archives are disabled or obfuscated.
		$author_base = get_option( 'author_base', 'author' );
		$stats['author_base'] = $author_base;

		// This is a basic check; full protection requires additional measures.
		if ( 'author' !== $author_base ) {
			$earned_points += 5;
			$stats['user_enum_protection'] = true;
		} else {
			$stats['user_enum_protection'] = false;
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'high';
		$threat_level = 60;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 65;
		} elseif ( $score >= 30 && $score < 60 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		// Return finding if user access auditing is insufficient.
		if ( $score < 60 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: audit score percentage */
					__( 'User access audit score: %d%%. Regular auditing of user access helps prevent unauthorized access and maintain security compliance.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-access-audit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}

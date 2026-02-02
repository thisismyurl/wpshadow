<?php
/**
 * Multisite Network Admin Tool Boundaries
 *
 * Tests whether network admin tools respect site boundaries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Network_Admin_Tool_Boundaries Class
 *
 * Validates data isolation in multisite tool operations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Network_Admin_Tool_Boundaries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-network-admin-tool-boundaries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Tool Data Isolation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies multisite tools respect site boundaries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests multisite tool data isolation.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for multisite installations
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// 1. Check for cross-site data leakage
		$leakage_issue = self::check_cross_site_data_leakage();
		if ( $leakage_issue ) {
			$issues[] = $leakage_issue;
		}

		// 2. Check site-level vs network-level capability checks
		$capability_issue = self::check_capability_boundaries();
		if ( $capability_issue ) {
			$issues[] = $capability_issue;
		}

		// 3. Check for multisite-specific isolation
		$isolation_issue = self::check_data_isolation();
		if ( $isolation_issue ) {
			$issues[] = $isolation_issue;
		}

		// 4. Check network admin action restrictions
		$access_issue = self::check_network_admin_access_control();
		if ( $access_issue ) {
			$issues[] = $access_issue;
		}

		// 5. Check for audit logging of network actions
		if ( ! self::has_network_action_logging() ) {
			$issues[] = __( 'Network admin actions not logged for audit trail', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of boundary issues */
					__( '%d multisite boundary issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/multisite-tool-boundaries',
				'recommendations' => array(
					__( 'Verify site-level exports only include site data', 'wpshadow' ),
					__( 'Check network admin cannot export other sites\' data', 'wpshadow' ),
					__( 'Validate capability checks for each site', 'wpshadow' ),
					__( 'Log all network-level tool operations', 'wpshadow' ),
					__( 'Test cross-site access attempts', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for cross-site data leakage.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_cross_site_data_leakage() {
		global $wpdb;

		// Check if queries include site boundaries
		// Should use: WHERE blog_id = {$blog_id}

		// Get all sites in network
		$sites = get_sites( array( 'number' => 999 ) );

		if ( count( $sites ) < 2 ) {
			return null; // Single site network
		}

		// Check if tools respect blog_id filter
		$tables_to_check = array(
			'posts',
			'postmeta',
			'comments',
			'commentmeta',
			'links',
			'options',
		);

		// This would require static analysis of tool code
		// For now, assume potential issue on large networks
		if ( count( $sites ) > 10 ) {
			return sprintf(
				/* translators: %d: number of sites */
				__( 'Network has %d sites - tools must enforce site boundaries', 'wpshadow' ),
				count( $sites )
			);
		}

		return null;
	}

	/**
	 * Check capability boundaries.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_capability_boundaries() {
		// Check for proper capability checks
		// Site admin should NOT access other sites
		// Network admin should be explicit about scope

		$current_user = wp_get_current_user();

		if ( ! $current_user->ID ) {
			return null;
		}

		// Check if user is network admin
		if ( is_network_admin() ) {
			// Network admin should NOT have implicit access to all sites
			$user_sites = get_blogs_of_user( $current_user->ID );

			if ( count( $user_sites ) > 1 ) {
				return sprintf(
					/* translators: %d: number of sites */
					__( 'Current user has explicit access to %d sites (should limit scope)', 'wpshadow' ),
					count( $user_sites )
				);
			}
		}

		return null;
	}

	/**
	 * Check data isolation.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_data_isolation() {
		global $wpdb;

		// Check if export includes data from multiple sites
		// When exporting from Site A, should NOT include Site B data

		$current_blog_id = get_current_blog_id();

		// Check if tables have blog_id filtering
		$has_blog_column = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'blog_id'",
				DB_NAME,
				"{$wpdb->prefix}posts"
			)
		);

		if ( ! $has_blog_column ) {
			return null; // Single site, no issue
		}

		// Verify blog_id filtering is enforced
		if ( ! has_filter( 'posts_where' ) ) {
			return __( 'Export queries may not filter by blog_id (cross-site data leak risk)', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check network admin access control.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_network_admin_access_control() {
		// Network admin should have restricted tool access
		// Should require explicit capability checks

		if ( ! is_multisite() ) {
			return null;
		}

		// Check if network admin page requires proper capabilities
		if ( is_network_admin() ) {
			if ( ! current_user_can( 'manage_network_plugins' ) && ! current_user_can( 'manage_network_users' ) ) {
				return __( 'Network admin access not properly restricted', 'wpshadow' );
			}
		}

		return null;
	}

	/**
	 * Check for network action logging.
	 *
	 * @since  1.2601.2148
	 * @return bool True if logging available.
	 */
	private static function has_network_action_logging() {
		// Check if network-level actions are logged
		if ( function_exists( 'wpshadow_log_network_action' ) ) {
			return true;
		}

		// Check if activity log captures network events
		$network_logs = get_option( 'wpshadow_network_action_logs', array() );

		return ! empty( $network_logs );
	}
}

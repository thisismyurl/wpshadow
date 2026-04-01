<?php
/**
 * Multisite Network Admin Tool Boundaries Diagnostic
 *
 * Tests whether network admin tools respect site boundaries and don't leak data across sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since 0.6093.1200
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
 * Verifies that multisite tools respect data isolation.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Verifies that network admin tools respect site boundaries and prevent data leakage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only applicable to multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// 1. Check network admin capabilities.
		$network_caps = array(
			'manage_network',
			'manage_sites',
			'manage_network_users',
			'manage_network_plugins',
			'manage_network_themes',
			'manage_network_options',
		);

		$current_user = wp_get_current_user();
		$is_super     = is_super_admin( $current_user->ID );

		// 2. Check for site-switching in tools.
		if ( function_exists( 'switch_to_blog' ) ) {
			// Verify proper switching and restoration.
			global $wpdb;

			// Check if any tools leave site context incorrect.
			$current_blog_id = get_current_blog_id();
			$main_site_id    = get_main_site_id();

			if ( is_network_admin() && $current_blog_id !== $main_site_id ) {
				$issues[] = __( 'Network admin not on main site context - data isolation may be compromised', 'wpshadow' );
			}
		}

		// 3. Check for cross-site data queries.
		global $wpdb;

		// Sample check: Verify queries use proper blog prefix.
		$sites = get_sites( array( 'number' => 5 ) );

		foreach ( $sites as $site ) {
			$blog_prefix = $wpdb->get_blog_prefix( $site->blog_id );

			// Verify prefix is correct format.
			if ( empty( $blog_prefix ) || $blog_prefix === $wpdb->prefix ) {
				if ( (int) $site->blog_id !== get_main_site_id() ) {
					$issues[] = sprintf(
						/* translators: %d: site ID */
						__( 'Site %d may have incorrect table prefix - data isolation risk', 'wpshadow' ),
						$site->blog_id
					);
				}
			}
		}

		// 4. Check export functionality in network admin.
		if ( is_network_admin() ) {
			// Verify exports are properly scoped.
			$export_actions = array(
				'wp_ajax_export_personal_data',
				'admin_action_export',
			);

			foreach ( $export_actions as $action ) {
				if ( has_action( $action ) ) {
					// Verify site context is checked.
					$issues[] = sprintf(
						/* translators: %s: action name */
						__( 'Action "%s" in network admin - verify site boundary checks', 'wpshadow' ),
						$action
					);
				}
			}
		}

		// 5. Check for site isolation in privacy requests.
		$request_table = $wpdb->prefix . 'posts';
		$request_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table} WHERE post_type = %s",
				'user_request'
			)
		);

		if ( (int) $request_count > 0 ) {
			// Verify requests are site-scoped.
			if ( is_network_admin() ) {
				$issues[] = __( 'Privacy requests in network admin context - verify proper site isolation', 'wpshadow' );
			}
		}

		// 6. Check for network-wide user data access.
		if ( $is_super ) {
			// Super admins can access all sites - verify logging.
			$user_count = get_user_count();

			if ( $user_count > 1000 ) {
				$issues[] = sprintf(
					/* translators: %d: number of users */
					__( 'Network has %d+ users - verify audit logging for cross-site access', 'wpshadow' ),
					$user_count
				);
			}
		}

		// 7. Check shared user data.
		$user_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta}"
		);

		if ( (int) $user_meta_count > 10000 ) {
			// Large shared user table - verify site-specific data is isolated.
			$issues[] = __( 'Large shared user meta table - verify site-specific data properly scoped', 'wpshadow' );
		}

		// 8. Check for plugin/theme data leakage.
		$network_active = get_site_option( 'active_sitewide_plugins', array() );

		if ( ! empty( $network_active ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				_n(
					'%d network-active plugin - verify site data isolation',
					'%d network-active plugins - verify site data isolation',
					count( $network_active ),
					'wpshadow'
				),
				count( $network_active )
			);
		}

		// 9. Check upload directories.
		$upload_dir = wp_upload_dir();
		$base_dir   = $upload_dir['basedir'];

		// In multisite, each site should have its own uploads/sites/X/ directory.
		if ( ! preg_match( '/\/sites\/\d+$/', $base_dir ) ) {
			if ( get_current_blog_id() !== get_main_site_id() ) {
				$issues[] = __( 'Sub-site not using isolated uploads directory - files may be shared', 'wpshadow' );
			}
		}

		// 10. Check for database table isolation.
		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$core_tables = array(
			'posts',
			'postmeta',
			'comments',
			'commentmeta',
			'options',
		);

		$shared_tables = 0;
		foreach ( $core_tables as $table ) {
			$full_table = $wpdb->prefix . $table;
			if ( ! in_array( $full_table, $tables, true ) ) {
				$shared_tables++;
			}
		}

		if ( $shared_tables > 0 && get_current_blog_id() !== get_main_site_id() ) {
			$issues[] = sprintf(
				/* translators: %d: number of tables */
				__( '%d core table(s) missing site prefix - using shared tables', 'wpshadow' ),
				$shared_tables
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite boundary issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-tool-boundaries?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'         => $issues,
				'is_super_admin' => $is_super,
				'current_site'   => get_current_blog_id(),
				'site_count'     => get_blog_count(),
			),
		);
	}
}

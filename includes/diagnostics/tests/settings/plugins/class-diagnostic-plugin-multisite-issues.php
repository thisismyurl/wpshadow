<?php
/**
 * Plugin Multisite Issues Diagnostic
 *
 * Detects plugins causing problems in multisite environments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Multisite Issues Class
 *
 * Identifies plugins incompatible with or causing issues in multisite.
 * Some plugins don't properly handle network activation or site switching.
 *
 * @since 1.5029.1630
 */
class Diagnostic_Plugin_Multisite_Issues extends Diagnostic_Base {

	protected static $slug        = 'plugin-multisite-issues';
	protected static $title       = 'Plugin Multisite Issues';
	protected static $description = 'Detects plugins incompatible with multisite';
	protected static $family      = 'plugins';

	public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$cache_key = 'wpshadow_plugin_multisite_issues';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get all plugins using WordPress API (NO $wpdb).
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$network_active = get_site_option( 'active_sitewide_plugins', array() );
		$problematic_plugins = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$issues = array();

			// Check if network activated.
			$is_network_active = array_key_exists( $plugin_path, $network_active );

			// Known problematic patterns.
			if ( $is_network_active ) {
				// Check for hardcoded URLs.
				$plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
				if ( file_exists( $plugin_file ) ) {
					$plugin_content = file_get_contents( $plugin_file );
					
					// Check for site_url() without get_current_blog_id().
					if ( strpos( $plugin_content, 'site_url()' ) !== false 
						&& strpos( $plugin_content, 'get_current_blog_id' ) === false ) {
						$issues[] = 'May use incorrect URLs in network';
					}

					// Check for direct DB prefix usage without switch_to_blog().
					if ( strpos( $plugin_content, '$wpdb->prefix' ) !== false 
						&& strpos( $plugin_content, 'switch_to_blog' ) === false ) {
						$issues[] = 'May query wrong database tables';
					}
				}
			}

			// Check for "Network: false" in header.
			if ( isset( $plugin_data['Network'] ) && false === $plugin_data['Network'] ) {
				$issues[] = 'Explicitly not multisite compatible';
			}

			if ( ! empty( $issues ) ) {
				$problematic_plugins[] = array(
					'name'            => $plugin_data['Name'],
					'slug'            => dirname( $plugin_path ),
					'version'         => $plugin_data['Version'],
					'network_active'  => $is_network_active,
					'issues'          => $issues,
				);
			}
		}

		if ( ! empty( $problematic_plugins ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of problematic plugins */
					__( '%d plugins may have multisite compatibility issues. Review for network activation.', 'wpshadow' ),
					count( $problematic_plugins )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multisite-plugin-compatibility',
				'data'         => array(
					'problematic_plugins' => $problematic_plugins,
					'total_plugins'       => count( $all_plugins ),
					'network_site_count'  => get_blog_count(),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

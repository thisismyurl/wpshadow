<?php
/**
 * Plugin Capability Conflicts Diagnostic
 *
 * Detects plugins requesting excessive or suspicious permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Capability Conflicts Class
 *
 * Analyzes plugin capability requirements and checks for conflicts.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Plugin_Capability_Conflicts extends Diagnostic_Base {

	protected static $slug        = 'plugin-capability-conflicts';
	protected static $title       = 'Plugin Capability Conflicts';
	protected static $description = 'Detects excessive permission requests';
	protected static $family      = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_capability_conflicts';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$conflicts      = array();

		// Dangerous capabilities that should be rare.
		$dangerous_caps = array(
			'delete_users',
			'remove_users',
			'promote_users',
			'create_users',
			'delete_plugins',
			'activate_plugins',
			'edit_plugins',
			'edit_themes',
			'delete_themes',
			'unfiltered_html',
			'unfiltered_upload',
		);

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$plugin_dir  = WP_PLUGIN_DIR . '/' . dirname( $plugin_path );
			$found_caps  = $this->scan_for_capabilities( $plugin_dir, $dangerous_caps );

			if ( ! empty( $found_caps ) ) {
				$conflicts[] = array(
					'name'         => $plugin_data['Name'],
					'slug'         => dirname( $plugin_path ),
					'capabilities' => $found_caps,
					'risk_level'   => count( $found_caps ) > 3 ? 'high' : 'medium',
				);
			}

			// Limit scans.
			if ( count( $conflicts ) >= 10 ) {
				break;
			}
		}

		if ( ! empty( $conflicts ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d plugins request excessive capabilities. Review permissions carefully.', 'wpshadow' ),
					count( $conflicts )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-capability-conflicts',
				'data'         => array(
					'plugins_with_conflicts' => $conflicts,
					'total_conflicts'        => count( $conflicts ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Scan plugin files for capability checks.
	 *
	 * @since  1.5030.1045
	 * @param  string $plugin_dir      Plugin directory.
	 * @param  array  $dangerous_caps  Capabilities to flag.
	 * @return array  Array of found capabilities.
	 */
	private static function scan_for_capabilities( $plugin_dir, $dangerous_caps ) {
		$found = array();
		$php_files = glob( $plugin_dir . '/*.php' );
		
		if ( ! empty( $php_files ) ) {
			$php_files = array_merge( $php_files, glob( $plugin_dir . '/**/*.php' ) );
		}

		// Limit to 20 files.
		$php_files = array_slice( $php_files, 0, 20 );

		foreach ( $php_files as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file );

			foreach ( $dangerous_caps as $cap ) {
				if ( preg_match( '/current_user_can\s*\(\s*[\'"]' . preg_quote( $cap, '/' ) . '[\'"]\s*\)/i', $content ) ) {
					$found[] = $cap;
				}
			}
		}

		return array_unique( $found );
	}
}

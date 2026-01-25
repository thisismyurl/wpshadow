<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Outdated Plugins
 *
 * Detects when installed plugins have available updates.
 * Outdated plugins may contain security vulnerabilities.
 *
 * @since 1.2.0
 */
class Test_Outdated_Plugins extends Diagnostic_Base {


	/**
	 * Check for outdated plugins
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$outdated = self::find_outdated_plugins();

		if ( empty( $outdated ) ) {
			return null;
		}

		$threat = min( 70, count( $outdated ) * 5 );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d plugins with available updates',
				count( $outdated )
			),
			'metadata'      => array(
				'outdated_count' => count( $outdated ),
				'plugins'        => array_slice( $outdated, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-plugin-updates/',
			'training_link' => 'https://wpshadow.com/training/keeping-plugins-updated/',
		);
	}

	/**
	 * Guardian Sub-Test: Outdated plugin count
	 *
	 * @return array Test result
	 */
	public static function test_outdated_count(): array {
		$outdated = self::find_outdated_plugins();

		return array(
			'test_name'      => 'Outdated Plugins Count',
			'outdated_count' => count( $outdated ),
			'passed'         => count( $outdated ) === 0,
			'description'    => empty( $outdated ) ? 'All plugins are up to date' : sprintf( '%d plugins need updates', count( $outdated ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Outdated plugin list
	 *
	 * @return array Test result
	 */
	public static function test_outdated_list(): array {
		$outdated = self::find_outdated_plugins();

		return array(
			'test_name'    => 'Outdated Plugins List',
			'plugin_count' => count( $outdated ),
			'plugins'      => array_slice( $outdated, 0, 10 ),
			'description'  => sprintf( 'Found %d plugins with updates', count( $outdated ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Critical security updates
	 *
	 * @return array Test result
	 */
	public static function test_critical_updates(): array {
		$outdated = self::find_outdated_plugins();
		$critical = array_filter( $outdated, fn( $p ) => isset( $p['is_security_update'] ) && $p['is_security_update'] );

		return array(
			'test_name'        => 'Critical Security Updates',
			'security_updates' => count( $critical ),
			'critical_plugins' => array_slice( $critical, 0, 5 ),
			'passed'           => count( $critical ) === 0,
			'description'      => empty( $critical ) ? 'No critical security updates' : sprintf( '%d plugins have CRITICAL security updates', count( $critical ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Update compatibility
	 *
	 * @return array Test result
	 */
	public static function test_update_compatibility(): array {
		$outdated             = self::find_outdated_plugins();
		$compatibility_issues = array();

		foreach ( $outdated as $plugin ) {
			$wp_version = get_bloginfo( 'version' );
			if ( isset( $plugin['requires'] ) && version_compare( $wp_version, $plugin['requires'], '<' ) ) {
				$compatibility_issues[] = array(
					'plugin'     => $plugin['name'],
					'requires'   => $plugin['requires'],
					'current_wp' => $wp_version,
				);
			}
		}

		return array(
			'test_name'            => 'Update Compatibility',
			'compatibility_issues' => $compatibility_issues,
			'issue_count'          => count( $compatibility_issues ),
			'passed'               => empty( $compatibility_issues ),
			'description'          => empty( $compatibility_issues ) ? 'All updates compatible' : sprintf( '%d updates may have compatibility issues', count( $compatibility_issues ) ),
		);
	}

	/**
	 * Find outdated plugins
	 *
	 * @return array List of outdated plugins
	 */
	private static function find_outdated_plugins(): array {
		$outdated       = array();
		$transient_key  = 'site_transient_update_plugins';
		$update_plugins = get_transient( $transient_key );

		if ( ! $update_plugins || ! isset( $update_plugins->response ) ) {
			return array();
		}

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $update_plugins->response as $plugin_file => $plugin_data ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$plugin_header = get_file_data(
					WP_PLUGIN_DIR . '/' . $plugin_file,
					array(
						'Name'    => 'Plugin Name',
						'Version' => 'Version',
					)
				);

				$outdated[] = array(
					'plugin_file'        => $plugin_file,
					'name'               => $plugin_header['Name'] ?? 'Unknown',
					'current_version'    => $plugin_header['Version'] ?? 'Unknown',
					'new_version'        => $plugin_data->new_version ?? 'Unknown',
					'slug'               => $plugin_data->slug ?? '',
					'is_security_update' => isset( $plugin_data->is_security_release ) && $plugin_data->is_security_release,
				);
			}
		}

		usort( $outdated, fn( $a, $b ) => ( $b['is_security_update'] ?? false ) <=> ( $a['is_security_update'] ?? false ) );

		return $outdated;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Outdated Plugins';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Identifies plugins that have available updates';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Updates';
	}
}

<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Unnecessary Plugins
 *
 * Detects plugins that may be unnecessary (duplicates, abandoned, conflicting).
 * Each plugin increases site complexity, attack surface, and maintenance burden.
 *
 * @since 1.2.0
 */
class Test_Unnecessary_Plugins extends Diagnostic_Base {


	/**
	 * Check for unnecessary plugins
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$issues = self::get_plugin_issues();

		if ( empty( $issues ) ) {
			return null;
		}

		$threat = count( $issues ) * 5;
		$threat = min( 75, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => $threat > 40 ? 'orange' : 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d potentially unnecessary plugins',
				count( $issues )
			),
			'metadata'      => array(
				'unnecessary_count' => count( $issues ),
				'issues_found'      => array_slice( $issues, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/plugin-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-plugin-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Plugin count analysis
	 *
	 * @return array Test result
	 */
	public static function test_plugin_count(): array {
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$plugin_count = count( $all_plugins );
		$active_count = count( $active_plugins );

		return array(
			'test_name'        => 'Plugin Count',
			'total_plugins'    => $plugin_count,
			'active_plugins'   => $active_count,
			'inactive_plugins' => $plugin_count - $active_count,
			'passed'           => $active_count < 20, // Reasonable limit
			'description'      => sprintf( '%d active of %d total plugins', $active_count, $plugin_count ),
		);
	}

	/**
	 * Guardian Sub-Test: Functionality duplicates
	 *
	 * @return array Test result
	 */
	public static function test_duplicate_functionality(): array {
		$issues = self::find_duplicate_plugins();

		return array(
			'test_name'       => 'Duplicate Functionality',
			'duplicate_count' => count( $issues ),
			'duplicates'      => $issues,
			'passed'          => empty( $issues ),
			'description'     => empty( $issues ) ? 'No duplicate plugins detected' : sprintf( 'Found %d plugins with duplicate functionality', count( $issues ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Abandoned plugins
	 *
	 * @return array Test result
	 */
	public static function test_abandoned_plugins(): array {
		$issues = self::find_abandoned_plugins();

		return array(
			'test_name'       => 'Abandoned Plugins',
			'abandoned_count' => count( $issues ),
			'abandoned_list'  => $issues,
			'passed'          => empty( $issues ),
			'description'     => empty( $issues ) ? 'No abandoned plugins detected' : sprintf( 'Found %d potentially abandoned plugins', count( $issues ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Plugin load analysis
	 *
	 * @return array Test result
	 */
	public static function test_plugin_load(): array {
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_data    = array();

		foreach ( array_slice( $active_plugins, 0, 10 ) as $plugin ) {
			$data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
			$plugin_data[] = array(
				'name'    => $data['Name'] ?? 'Unknown',
				'version' => $data['Version'] ?? 'Unknown',
				'author'  => $data['Author'] ?? 'Unknown',
			);
		}

		return array(
			'test_name'    => 'Active Plugins (Top 10)',
			'plugin_count' => count( get_option( 'active_plugins', array() ) ),
			'top_plugins'  => $plugin_data,
			'description'  => sprintf( '%d plugins currently active', count( get_option( 'active_plugins', array() ) ) ),
		);
	}

	/**
	 * Get all plugin issues
	 *
	 * @return array List of plugin issues
	 */
	private static function get_plugin_issues(): array {
		$issues = array();

		$issues = array_merge( $issues, self::find_duplicate_plugins() );
		$issues = array_merge( $issues, self::find_abandoned_plugins() );

		return $issues;
	}

	/**
	 * Find plugins with duplicate functionality
	 *
	 * @return array Duplicate plugin issues
	 */
	private static function find_duplicate_plugins(): array {
		$duplicates = array();

		// Common plugin pairs that duplicate functionality
		$duplicate_pairs = array(
			array( 'jetpack', 'akismet' ),           // Similar spam/security features
			array( 'yoast', 'rank-math' ),           // Both SEO plugins
			array( 'woocommerce', 'easy-digital-downloads' ), // E-commerce
			array( 'elementor', 'divi' ),            // Page builders
			array( 'wpcf7', 'ninja-forms' ),         // Contact forms
		);

		$active_plugins = array_map( fn( $p ) => strtolower( $p ), get_option( 'active_plugins', array() ) );

		foreach ( $duplicate_pairs as $pair ) {
			$found = array();
			foreach ( $pair as $plugin ) {
				foreach ( $active_plugins as $active ) {
					if ( strpos( $active, $plugin ) !== false ) {
						$found[] = $active;
					}
				}
			}

			if ( count( $found ) > 1 ) {
				$duplicates[] = array(
					'type'    => 'Duplicate functionality',
					'plugins' => $found,
				);
			}
		}

		return $duplicates;
	}

	/**
	 * Find potentially abandoned plugins
	 *
	 * @return array Abandoned plugin issues
	 */
	private static function find_abandoned_plugins(): array {
		$abandoned = array();

		// Common abandoned/deprecated plugins
		$abandoned_list = array(
			'wp-security-audit-log' => 'Deprecated - use alternative',
			'wordpress-seo'         => 'Superseded by Yoast SEO',
			'all-in-one-seo-pack'   => 'Outdated version detected',
		);

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $abandoned_list as $plugin => $reason ) {
			foreach ( $active_plugins as $active ) {
				if ( strpos( $active, $plugin ) !== false ) {
					$abandoned[] = array(
						'plugin' => $active,
						'reason' => $reason,
					);
				}
			}
		}

		return $abandoned;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Unnecessary Plugins';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Identifies plugins with duplicate functionality or those that may be unnecessary';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}

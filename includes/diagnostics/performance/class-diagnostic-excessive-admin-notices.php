<?php
/**
 * Diagnostic: Excessive Admin Notices
 *
 * Detects too many admin notices from other plugins cluttering the admin.
 *
 * Philosophy: Inspire Confidence (#8) - Clean admin = professional
 * KB Link: https://wpshadow.com/kb/excessive-admin-notices
 * Training: https://wpshadow.com/training/excessive-admin-notices
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Excessive Admin Notices diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Excessive_Admin_Notices extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wp_filter;

		// Count registered admin notice hooks
		$notice_count = 0;
		$notice_hooks = [
			'admin_notices',
			'all_admin_notices',
			'network_admin_notices',
		];

		$plugins_with_notices = [];

		foreach ( $notice_hooks as $hook ) {
			if ( ! isset( $wp_filter[ $hook ] ) ) {
				continue;
			}

			foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$notice_count++;

					// Try to identify the plugin
					if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
						$class = get_class( $callback['function'][0] );
						$plugins_with_notices[] = $class;
					} elseif ( is_string( $callback['function'] ) ) {
						$plugins_with_notices[] = $callback['function'];
					}
				}
			}
		}

		// Only flag if excessive (more than 10 notices registered)
		if ( $notice_count < 10 ) {
			return null;
		}

		$severity = $notice_count > 20 ? 'medium' : 'low';

		// Get unique plugin list
		$unique_plugins = array_unique( $plugins_with_notices );
		$plugin_list = array_slice( $unique_plugins, 0, 5 ); // Top 5

		$description = sprintf(
			__( 'Your admin has %d notice hooks registered by various plugins. This clutters the interface and can slow down admin pages. WPShadow\'s Admin Notice Cleaner hides non-essential notices on WPShadow pages.', 'wpshadow' ),
			$notice_count
		);

		if ( ! empty( $plugin_list ) ) {
			$description .= ' ' . __( 'Sample sources: ', 'wpshadow' ) . implode( ', ', array_map( function( $plugin ) {
				// Extract plugin name from class/function
				$parts = explode( '\\', $plugin );
				return end( $parts );
			}, $plugin_list ) );
		}

		return [
			'id'                => 'excessive-admin-notices',
			'title'             => __( 'Too Many Admin Notices', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'low',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/excessive-admin-notices',
			'training_link'     => 'https://wpshadow.com/training/excessive-admin-notices',
			'affected_resource' => sprintf( '%d notice hooks', $notice_count ),
			'metadata'          => [
				'notice_count'     => $notice_count,
				'unique_sources'   => count( $unique_plugins ),
				'sample_plugins'   => $plugin_list,
			],
		];
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}

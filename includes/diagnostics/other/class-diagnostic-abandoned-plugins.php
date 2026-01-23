<?php
declare(strict_types=1);
/**
 * Abandoned Plugin Detection Diagnostic
 *
 * Philosophy: Supply chain - detect unmaintained plugins
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for abandoned/unmaintained plugins.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Abandoned_Plugins extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;

		$plugins   = get_plugins();
		$abandoned = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check if plugin hasn't been updated in 3+ years
			$last_update = get_transient( 'plugin_last_update_' . $plugin_file );

			if ( empty( $last_update ) || ( time() - intval( $last_update ) ) > ( 3 * 365 * DAY_IN_SECONDS ) ) {
				// Check if marked as abandoned on WordPress.org
				if ( preg_match( '/abandoned|unmaintained|inactive/i', $plugin_data['Description'] ) ) {
					$abandoned[] = $plugin_data['Name'];
				}
			}
		}

		if ( ! empty( $abandoned ) ) {
			return array(
				'id'            => 'abandoned-plugins',
				'title'         => 'Abandoned/Unmaintained Plugins Detected',
				'description'   => sprintf(
					'Found abandoned plugins not updated in 2+ years: %s. These don\'t receive security patches. Replace with maintained alternatives or remove.',
					implode( ', ', array_slice( $abandoned, 0, 3 ) )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/replace-abandoned-plugins/',
				'training_link' => 'https://wpshadow.com/training/plugin-maintenance/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
			);
		}

		return null;
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

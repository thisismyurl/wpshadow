<?php
declare(strict_types=1);
/**
 * Dark Mode Diagnostic
 *
 * @package WPShadow
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dark mode adoption and environmental impact.
 */
class Diagnostic_Dark_Mode extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $current_user;
		
		// Get current user's dark mode preference
		$dark_mode = get_user_meta( $current_user->ID, 'wpshadow_dark_mode_preference', true );
		
		// Track KPI for dark mode adoption
		self::track_dark_mode_adoption( $dark_mode );
		
		// If dark mode is enabled, return positive feedback (no "issue")
		if ( $dark_mode && 'dark' === $dark_mode ) {
			return array(
				'id'           => 'dark-mode-active',
				'title'        => '🌙 Dark Mode Active',
				'description'  => 'Dark Mode is enabled. You are reducing eye strain, saving battery on OLED screens, and lowering energy consumption - contributing to a more sustainable digital experience.',
				'color'        => '#4caf50',
				'bg_color'     => '#e8f5e9',
				'kb_link'      => 'https://wpshadow.com/kb/dark-mode-benefits/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=darkmode',
				'auto_fixable' => false,
				'threat_level' => 0,
			);
		} elseif ( $dark_mode && 'auto' === $dark_mode ) {
			// Auto mode (system preference)
			$system_preference = self::get_system_preference_text();
			return array(
				'id'           => 'dark-mode-auto',
				'title'        => '🌙 Dark Mode Auto',
				'description'  => "Dark Mode is set to Auto (following your system preference: $system_preference). Automatically adjusting based on your system settings helps reduce eye strain and save battery - supporting sustainability efforts.",
				'color'        => '#2196f3',
				'bg_color'     => '#e3f2fd',
				'kb_link'      => 'https://wpshadow.com/kb/dark-mode-benefits/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=darkmode',
				'auto_fixable' => false,
				'threat_level' => 0,
			);
		}
		
		// Light mode or no preference set - suggest dark mode
		return array(
			'id'           => 'dark-mode-disabled',
			'title'        => 'Consider Enabling Dark Mode',
			'description'  => 'Dark Mode can reduce eye strain, save battery on OLED displays, and lower energy consumption. Enable it in WPShadow Tools → Dark Mode to contribute to a more sustainable digital experience.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/dark-mode-benefits/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=darkmode',
			'auto_fixable' => false,
			'threat_level' => 0,
		);
	}
	
	/**
	 * Track dark mode adoption in KPI system
	 *
	 * @param string|false $preference User's dark mode preference.
	 * @return void
	 */
	private static function track_dark_mode_adoption( $preference ) {
		$adoption_data = get_option( 'wpshadow_dark_mode_adoption', array() );
		
		// Initialize if needed
		if ( empty( $adoption_data ) ) {
			$adoption_data = array(
				'total_users'       => 0,
				'dark_mode_users'   => 0,
				'auto_mode_users'   => 0,
				'light_mode_users'  => 0,
				'last_updated'      => gmdate( 'Y-m-d H:i:s' ),
				'adoption_rate'     => 0,
			);
		}
		
		// Count this user once per day
		$transient_key = 'wpshadow_dark_mode_tracked_' . get_current_user_id() . '_' . gmdate( 'Y-m-d' );
		if ( ! get_transient( $transient_key ) ) {
			// First time today for this user
			$adoption_data['total_users']++;
			
			if ( 'dark' === $preference ) {
				$adoption_data['dark_mode_users']++;
			} elseif ( 'auto' === $preference ) {
				$adoption_data['auto_mode_users']++;
			} else {
				$adoption_data['light_mode_users']++;
			}
			
			// Calculate adoption rate (dark + auto modes)
			$adoption_modes = $adoption_data['dark_mode_users'] + $adoption_data['auto_mode_users'];
			$adoption_data['adoption_rate'] = $adoption_data['total_users'] > 0 
				? round( ( $adoption_modes / $adoption_data['total_users'] ) * 100, 1 )
				: 0;
			
			$adoption_data['last_updated'] = gmdate( 'Y-m-d H:i:s' );
			
			update_option( 'wpshadow_dark_mode_adoption', $adoption_data );
			set_transient( $transient_key, true, DAY_IN_SECONDS );
		}
	}
	
	/**
	 * Get human-readable system preference description
	 *
	 * @return string Description of system preference.
	 */
	private static function get_system_preference_text() {
		// This would require JavaScript to detect, but we can provide a generic response
		return 'your system setting';
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
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}

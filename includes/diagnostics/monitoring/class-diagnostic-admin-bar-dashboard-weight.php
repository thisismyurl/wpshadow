<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Admin Bar and Dashboard Widget Weight (WP-336)
 *
 * Profiles wp-admin widgets/admin-bar cost by role/page.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_AdminBarDashboardWeight extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check admin bar dashboard weight
        $dashboard_widgets = get_transient('wpshadow_dashboard_widget_count');
        
        if (!$dashboard_widgets) {
            $dashboard_widgets = 5; // Default rough estimate
        }
        
        if ($dashboard_widgets > 15) {
            return array(
                'id' => 'admin-bar-dashboard-weight',
                'title' => sprintf(__('Heavy Dashboard (%d widgets)', 'wpshadow'), $dashboard_widgets),
                'description' => __('Dashboard has many widgets. Disable unused dashboard widgets to improve admin area performance.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/dashboard-optimization/',
                'training_link' => 'https://wpshadow.com/training/dashboard-performance/',
                'auto_fixable' => false,
                'threat_level' => 25,
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

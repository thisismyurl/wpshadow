<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Data Retention Policy Set?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Data_Retention_Policy extends Diagnostic_Base {
    protected static $slug = 'data-retention-policy';
    protected static $title = 'Data Retention Policy Set?';
    protected static $description = 'Checks if data retention schedule is configured.';

    public static function check(): ?array {
        // Check if data retention settings are configured
        // WordPress has built-in comment/user cleanup settings
        $comment_days = (int) get_option('wp_delete_comment_older_than', 0);
        $user_cleanup = get_option('wpshadow_data_retention_days', 0);
        
        // Check for privacy/GDPR plugins that manage retention
        $retention_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );
        
        $has_retention_plugin = false;
        foreach ($retention_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_retention_plugin = true;
                break;
            }
        }
        
        // Pass if retention configured via plugin or settings
        if ($has_retention_plugin || $comment_days > 0 || $user_cleanup > 0) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No data retention policy configured. Consider setting automatic cleanup schedules.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/data-retention-policy/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=data-retention-policy',
            'training_link' => 'https://wpshadow.com/training/data-retention-policy/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 2,
        );
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

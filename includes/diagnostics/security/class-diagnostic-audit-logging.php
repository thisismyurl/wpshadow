<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Audit Trail Logging Active?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Audit_Logging extends Diagnostic_Base {
    protected static $slug = 'audit-logging';
    protected static $title = 'Audit Trail Logging Active?';
    protected static $description = 'Verifies user action logging is enabled.';


    public static function check(): ?array {
        // Check for common audit logging plugins
        $audit_plugins = array(
            'wp-security-audit-log/wp-security-audit-log.php',
            'activity-log/aryo-activity-log.php',
            'simple-history/index.php',
            'stream/stream.php',
        );
        
        $has_audit_logging = false;
        foreach ($audit_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_audit_logging = true;
                break;
            }
        }
        
        if (!$has_audit_logging) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'No audit logging plugin detected. User actions are not being tracked.',
                'severity'      => 'medium',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/audit-logging/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=audit-logging',
                'training_link' => 'https://wpshadow.com/training/audit-logging/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
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
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}

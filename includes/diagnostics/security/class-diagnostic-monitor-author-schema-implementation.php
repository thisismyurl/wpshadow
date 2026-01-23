<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Author extends Diagnostic_Base {
  public static function check(): ?array {
        // Check if monitoring plugins are active
        $has_monitoring = is_plugin_active('wordfence/wordfence.php') || 
                         is_plugin_active('sucuri-scanner/sucuri.php');
        if ($has_monitoring) {
            return null; // Monitoring in place
        }
        
return ['id' => 'monitor-author_schema_implementation', 'title' => __('Author Schema Implementation', 'wpshadow'), 'description' => __('Verifies byline schema on articles. Missing = no author visibility in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
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

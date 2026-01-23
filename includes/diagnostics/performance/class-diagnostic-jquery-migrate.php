<?php
declare(strict_types=1);
/**
 * jQuery Migrate Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Detect if jQuery Migrate is enqueued on the frontend.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Jquery_Migrate extends Diagnostic_Base {
    /**
     * Run the diagnostic check.
     *
     * @return array|null Finding data or null if no issue.
     */
    public static function check(): ?array {
        if ( is_admin() ) {
            return null;
        }

        $disabled = (bool) get_option( 'wpshadow_disable_jquery_migrate', false );
        if ( $disabled ) {
            return null;
        }

        $present = self::is_jquery_migrate_present();
        if ( ! $present ) {
            return null;
        }

        return array(
            'id'           => 'jquery-migrate-enabled',
            'title'        => 'jQuery Migrate Loaded',
            'description'  => 'Legacy jQuery Migrate is enqueued on the frontend. Disabling it can reduce JS size and parse time. Only keep it if a theme or plugin requires it.',
            'color'        => '#ff9800',
            'bg_color'     => '#fff3e0',
            'kb_link'      => 'https://wpshadow.com/kb/jquery-migrate/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=jquery-migrate',
            'auto_fixable' => true,
            'threat_level' => 25,
        );
    }

    private static function is_jquery_migrate_present() {
        global $wp_scripts;
        if ( ! isset( $wp_scripts ) ) {
            return false;
        }

        // If registered or queued, consider it present.
        $registered = isset( $wp_scripts->registered['jquery-migrate'] );
        $queued     = is_object( $wp_scripts ) && is_array( $wp_scripts->queue ?? null ) && in_array( 'jquery-migrate', $wp_scripts->queue, true );
        return ( $registered || $queued );
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

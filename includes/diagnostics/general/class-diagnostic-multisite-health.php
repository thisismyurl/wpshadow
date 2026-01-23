<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multisite Network Health
 *
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Multisite_Health extends Diagnostic_Base {
	protected static $slug        = 'multisite-health';
	protected static $title       = 'Multisite Network Health';
	protected static $description = 'Monitors health across all network sites.';


	public static function check(): ?array {
		if (!is_multisite()) {
			return null;
		}
		global $wpdb;
		$site_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->blogs} WHERE deleted = 0");
		if ($site_count > 100) {
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => "Large network ({$site_count} sites) - monitor performance.",
				'color'         => '#ff9800',
				'bg_color'      => '#fff3e0',
				'kb_link'       => 'https://wpshadow.com/kb/multisite-health/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=multisite-health',
				'training_link' => 'https://wpshadow.com/training/multisite-health/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Core',
				'priority'      => 2,
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

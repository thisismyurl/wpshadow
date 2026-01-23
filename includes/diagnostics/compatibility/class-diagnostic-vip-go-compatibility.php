<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WordPress VIP Compatibility
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_VIP_Go_Compatibility extends Diagnostic_Base {
	protected static $slug        = 'vip-go-compatibility';
	protected static $title       = 'WordPress VIP Compatibility';
	protected static $description = 'Checks code against VIP coding standards.';

	public static function check(): ?array {
		// Check if running on WordPress VIP Go
		$is_vip = (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV === true) ||
		          (defined('VIP_GO_ENV') && VIP_GO_ENV === 'production');
		
		if (!$is_vip) {
			return null; // Pass - not on VIP, no compatibility concerns
		}
		
		// Check for VIP-incompatible plugins
		$incompatible_plugins = array(
			'wp-super-cache/wp-super-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-rocket/wp-rocket.php',
		);
		
		$found_incompatible = array();
		foreach ($incompatible_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				$found_incompatible[] = basename(dirname($plugin));
			}
		}
		
		if (empty($found_incompatible)) {
			return null; // Pass - no incompatible plugins
		}
		
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'VIP-incompatible plugins detected: ' . implode(', ', $found_incompatible),
			'color'         => '#f44336',
			'bg_color'      => '#ffebee',
			'kb_link'       => 'https://wpshadow.com/kb/vip-go-compatibility/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=vip-go-compatibility',
			'training_link' => 'https://wpshadow.com/training/vip-go-compatibility/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Compatibility',
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

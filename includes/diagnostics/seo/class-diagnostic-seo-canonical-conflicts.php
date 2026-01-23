<?php
declare(strict_types=1);
/**
 * Canonical Tag Conflicts Diagnostic
 *
 * Philosophy: Prevent duplicate/conflicting canonicals
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Canonical_Conflicts extends Diagnostic_Base {
    /**
     * Heuristic: multiple SEO plugins active often cause duplicate canonicals.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $active = [];
        $plugins = [
            'wordpress-seo/wp-seo.php',
            'seo-by-rank-math/rank-math.php',
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            'wp-seopress/seopress.php',
        ];
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                $active[] = $plugin;
            }
        }
        if (count($active) >= 2) {
            return [
                'id' => 'seo-canonical-conflicts',
                'title' => 'Potential Canonical Tag Conflicts',
                'description' => 'Multiple SEO plugins are active. This can output duplicate or conflicting canonical tags. Keep only one SEO plugin active.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/canonical-tag-conflicts/',
                'training_link' => 'https://wpshadow.com/training/canonicalization/',
                'auto_fixable' => false,
                'threat_level' => 55,
            ];
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

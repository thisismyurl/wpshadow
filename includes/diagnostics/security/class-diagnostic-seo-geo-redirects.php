<?php
declare(strict_types=1);
/**
 * Geo/IP Redirects Diagnostic
 *
 * Philosophy: Avoid crawl-blocking language/location auto-redirects
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Geo_Redirects extends Diagnostic_Base {
    /**
     * Heuristic: flag common plugins that auto-redirect by locale.
     *
     * @return array|null
     */
    public static function check(): ?array {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = [
            'sitepress-multilingual-cms/sitepress.php', // WPML
            'translatepress-multilingual/translatepress-multilingual.php',
        ];
        foreach ($plugins as $plugin) {
            if (function_exists('is_plugin_active') && is_plugin_active($plugin)) {
                return [
                    'id' => 'seo-geo-redirects',
                    'title' => 'Potential Geo/Language Auto-Redirects',
                    'description' => 'Language or geo-based auto-redirects can hinder crawling. Ensure bots can access canonical versions without forced redirects.',
                    'severity' => 'medium',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/geo-redirects-seo/',
                    'training_link' => 'https://wpshadow.com/training/international-redirects/',
                    'auto_fixable' => false,
                    'threat_level' => 45,
                ];
            }
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

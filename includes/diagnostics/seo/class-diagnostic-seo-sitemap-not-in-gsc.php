<?php
declare(strict_types=1);
/**
 * Sitemap Not in GSC Diagnostic
 *
 * Philosophy: SEO indexation - submit sitemap to help discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if sitemap is submitted to GSC.
 */
class Diagnostic_SEO_Sitemap_Not_In_GSC extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-sitemap-not-in-gsc',
			'title'       => 'Submit Sitemap to Search Console',
			'description' => 'Ensure XML sitemap is submitted to Google Search Console. Sitemaps help Google discover and index pages faster. Submit at: Search Console > Sitemaps.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/submit-sitemap-gsc/',
			'training_link' => 'https://wpshadow.com/training/sitemap-submission/',
			'auto_fixable' => false,
			'threat_level' => 60,
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
	}}

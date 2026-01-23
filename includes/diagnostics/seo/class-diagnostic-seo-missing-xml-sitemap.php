<?php
declare(strict_types=1);
/**
 * Missing XML Sitemap Diagnostic
 *
 * Philosophy: SEO indexation - sitemaps help discovery
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing XML sitemap.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_XML_Sitemap extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_get( $sitemap_url, array( 'timeout' => 5 ) );
		
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			// Check for wp-sitemap.xml (WordPress core)
			$wp_sitemap = home_url( '/wp-sitemap.xml' );
			$wp_response = wp_remote_get( $wp_sitemap, array( 'timeout' => 5 ) );
			
			if ( is_wp_error( $wp_response ) || wp_remote_retrieve_response_code( $wp_response ) !== 200 ) {
				return array(
					'id'          => 'seo-missing-xml-sitemap',
					'title'       => 'Missing XML Sitemap',
					'description' => 'No XML sitemap detected. Sitemaps help search engines discover and index your content. Enable WordPress core sitemaps or use an SEO plugin.',
					'severity'    => 'high',
					'category'    => 'seo',
					'kb_link'     => 'https://wpshadow.com/kb/create-xml-sitemap/',
					'training_link' => 'https://wpshadow.com/training/sitemap-optimization/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
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
	}}

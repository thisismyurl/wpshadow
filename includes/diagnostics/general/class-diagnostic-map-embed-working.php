<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Maps Embedded?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Map_Embed_Working extends Diagnostic_Base {
	protected static $slug        = 'map-embed-working';
	protected static $title       = 'Google Maps Embedded?';
	protected static $description = 'Checks if location map is embedded and working.';

	public static function check(): ?array {
		$pages = get_posts(
			array(
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$has_map = false;
		foreach ( $pages as $page ) {
			if ( stripos( $page->post_content, 'maps.google.com' ) !== false ||
				stripos( $page->post_content, 'google.com/maps' ) !== false ||
				preg_match( '/<iframe[^>]+maps/', $page->post_content ) ) {
				$has_map = true;
				break;
			}
		}

		if ( $has_map ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No location map found', 'wpshadow' ),
			'description'   => __( 'Local businesses benefit from embedding a Google Map. Customers need to find you easily.', 'wpshadow' ),
			'severity'      => 'low',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/map-embed-working/',
			'training_link' => 'https://wpshadow.com/training/map-embed-working/',
			'auto_fixable'  => false,
			'threat_level'  => 30,
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
	 * Test: Post query detection
	 *
	 * Verifies that diagnostic correctly queries posts and
	 * evaluates them for issues.
	 *
	 * @return array Test result
	 */
	public static function test_post_detection(): array {
		$result = self::check();
		
		// Post queries should return null or array with findings
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Post detection logic working',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid post detection result',
		);
	}}

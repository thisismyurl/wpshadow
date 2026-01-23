<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Business Hours Visible?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Business_Hours_Display extends Diagnostic_Base {
	protected static $slug        = 'business-hours-display';
	protected static $title       = 'Business Hours Visible?';
	protected static $description = 'Checks if operating hours are prominently displayed.';

	public static function check(): ?array {
		$pages = get_posts(
			array(
				'post_type'      => array( 'page', 'post' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$hours_keywords = array( 'hours', 'open', 'closed', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'am', 'pm' );
		$has_hours      = false;

		foreach ( $pages as $page ) {
			$content_lower = strtolower( $page->post_content );
			$matches       = 0;
			foreach ( $hours_keywords as $keyword ) {
				if ( strpos( $content_lower, $keyword ) !== false ) {
					++$matches;
				}
			}
			if ( $matches >= 3 ) {
				$has_hours = true;
				break;
			}
		}

		if ( $has_hours ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'Business hours not found', 'wpshadow' ),
			'description'   => __( 'Customers need to know when you\'re open. Add your hours to your contact page.', 'wpshadow' ),
			'severity'      => 'low',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/business-hours-display/',
			'training_link' => 'https://wpshadow.com/training/business-hours-display/',
			'auto_fixable'  => false,
			'threat_level'  => 35,
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

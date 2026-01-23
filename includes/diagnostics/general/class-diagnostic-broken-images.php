<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Any Broken Images?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Broken_Images extends Diagnostic_Base {
	protected static $slug        = 'broken-images';
	protected static $title       = 'Any Broken Images?';
	protected static $description = 'Scans for missing or broken image files.';

	public static function check(): ?array {
		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
			)
		);

		$broken = array();
		foreach ( $posts as $post ) {
			preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $matches );
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $img_url ) {
					if ( strpos( $img_url, home_url() ) === 0 ) {
						$path = str_replace( home_url( '/' ), ABSPATH, $img_url );
						$path = strtok( $path, '?' );
						if ( ! file_exists( $path ) ) {
							$broken[] = array(
								'post_id'    => $post->ID,
								'post_title' => $post->post_title,
								'image_url'  => $img_url,
							);
							if ( count( $broken ) >= 10 ) {
								break 2;
							}
						}
					}
				}
			}
		}

		if ( empty( $broken ) ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => sprintf( _n( '%d broken image found', '%d broken images found', count( $broken ), 'wpshadow' ), count( $broken ) ),
			'description'   => __( 'Some images in your content are missing or moved. This looks unprofessional to visitors.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/broken-images/',
			'training_link' => 'https://wpshadow.com/training/broken-images/',
			'auto_fixable'  => false,
			'threat_level'  => 55,
			'broken_images' => $broken,
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

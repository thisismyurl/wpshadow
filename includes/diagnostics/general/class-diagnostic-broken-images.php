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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Any Broken Images?
	 * Slug: broken-images
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Scans for missing or broken image files.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_broken_images(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

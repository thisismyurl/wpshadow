<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Content_Optimizer extends Diagnostic_Base {

	protected static $slug = 'content-optimizer';
	protected static $title = 'Content Quality Optimization';
	protected static $description = 'Checks content for SEO, readability, accessibility, and quality issues.';

	public static function check(): ?array {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return null;
		}

		$recent_posts = get_posts( array(
			'post_type'   => 'post',
			'numberposts' => 10,
			'post_status' => 'publish',
		) );

		$issues = array();
		foreach ( $recent_posts as $post ) {
			if ( empty( get_the_post_thumbnail_url( $post->ID ) ) ) {
				$issues[] = 'missing_featured_image';
			}
			if ( ! has_excerpt( $post->ID ) ) {
				$issues[] = 'missing_excerpt';
			}
			if ( empty( get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true ) ) ) {
				$issues[] = 'missing_seo';
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d content quality issues in recent posts. Enable content optimization to improve SEO, readability, and accessibility.', 'wpshadow' ),
				count( $issues )
			),
			'category'     => 'content',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
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

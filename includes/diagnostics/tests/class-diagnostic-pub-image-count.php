<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Images
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Do posts have images for visual content?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Continuous batch implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Image_Count extends Diagnostic_Base {
	protected static $slug = 'pub-image-count';
	protected static $title = 'Posts Have Images';
	protected static $description = 'Do posts have images for visual content?';
	protected static $category = 'Content Publishing';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get recent published posts
		$posts = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC'
		] );

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_images = 0;
		foreach ( $posts as $post ) {
			// Check for images in content
			$content = $post->post_content;
			if ( preg_match( '/<img\s+/', $content ) || preg_match( '/\[gallery/', $content ) ) {
				$posts_with_images++;
			}
		}

		$percentage = ( $posts_with_images / count( $posts ) ) * 100;

		if ( $percentage < 50 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-image-count',
				'Posts Lack Images',
				sprintf( 'Only %.0f%% of recent posts contain images. Images improve engagement and break up text.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

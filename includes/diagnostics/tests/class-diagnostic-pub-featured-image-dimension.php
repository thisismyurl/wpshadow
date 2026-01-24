<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Featured Images
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Do published posts have featured images?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5+ implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Featured_Image_Dimension extends Diagnostic_Base {
	protected static $slug = 'pub-featured-image-dimension';
	protected static $title = 'Posts Have Featured Images';
	protected static $description = 'Do published posts have featured images?';
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
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'date_query'     => [
				[
					'after'  => '6 months ago',
					'column' => 'post_date'
				]
			]
		] );

		if ( empty( $posts ) ) {
			return null; // No recent posts
		}

		$posts_with_featured = 0;
		foreach ( $posts as $post_id ) {
			if ( has_post_thumbnail( $post_id ) ) {
				$posts_with_featured++;
			}
		}

		$percentage = ( $posts_with_featured / count( $posts ) ) * 100;

		if ( $percentage < 70 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-featured-image-dimension',
				'Posts Missing Featured Images',
				sprintf( 'Only %.0f%% of your recent posts have featured images. Featured images improve engagement and make posts more shareable on social media.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

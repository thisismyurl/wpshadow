<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Post Content Length
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Are posts substantive in length (not too short)?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Continuous batch implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Content_Length extends Diagnostic_Base {
	protected static $slug         = 'pub-content-length';
	protected static $title        = 'Post Content Length';
	protected static $description  = 'Are posts substantive in length?';
	protected static $category     = 'Content Publishing';
	protected static $threat_level = 'low';
	protected static $family       = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array {
		// Get recent published posts
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$short_posts = 0;
		foreach ( $posts as $post ) {
			// Remove HTML tags and count words
			$content    = wp_strip_all_tags( $post->post_content );
			$word_count = str_word_count( $content );
			// Posts should be at least 300 words for good SEO
			if ( $word_count < 300 ) {
				++$short_posts;
			}
		}

		$percentage = ( $short_posts / count( $posts ) ) * 100;

		if ( $percentage > 30 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-content-length',
				'Posts Too Short',
				sprintf( '%.0f%% of recent posts are under 300 words. Longer content typically ranks better and provides more value.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

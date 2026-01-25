<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Tags
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Are published posts assigned relevant tags?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Tags_Added extends Diagnostic_Base {
	protected static $slug         = 'pub-tags-added';
	protected static $title        = 'Posts Have Tags';
	protected static $description  = 'Are published posts assigned relevant tags?';
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
		// Get all published posts
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No posts to check
		}

		// Check how many posts have at least 3 tags
		$posts_with_tags = 0;
		foreach ( $posts as $post_id ) {
			$tags = get_the_tags( $post_id );
			if ( is_array( $tags ) && count( $tags ) >= 3 ) {
				++$posts_with_tags;
			}
		}

		$percentage = ( $posts_with_tags / count( $posts ) ) * 100;

		if ( $percentage < 50 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-tags-added',
				'Posts Lack Tags',
				sprintf( 'Only %.0f%% of your published posts have 3+ tags. Tags improve SEO and help readers discover related content.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

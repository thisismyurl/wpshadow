<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Category Assignment
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Are posts assigned to appropriate categories?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Category_Assigned extends Diagnostic_Base {
	protected static $slug = 'pub-category-assigned';
	protected static $title = 'Posts Have Category Assignment';
	protected static $description = 'Are posts assigned to appropriate categories?';
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
		// Get all published posts
		$posts = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids'
		] );

		if ( empty( $posts ) ) {
			return null; // No posts to check
		}

		// Check how many posts are in a category (not just Uncategorized)
		$posts_with_category = 0;
		foreach ( $posts as $post_id ) {
			$categories = get_the_category( $post_id );
			if ( is_array( $categories ) && ! empty( $categories ) ) {
				// Check if assigned to something other than Uncategorized
				$in_category = false;
				foreach ( $categories as $cat ) {
					if ( 'uncategorized' !== $cat->slug ) {
						$in_category = true;
						break;
					}
				}
				if ( $in_category ) {
					$posts_with_category++;
				}
			}
		}

		$percentage = ( $posts_with_category / count( $posts ) ) * 100;

		if ( $percentage < 70 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-category-assigned',
				'Posts Not Properly Categorized',
				sprintf( 'Only %.0f%% of your posts are assigned to specific categories (not just Uncategorized). Proper categorization improves navigation and SEO.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

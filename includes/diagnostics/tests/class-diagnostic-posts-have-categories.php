<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Posts Have Categories
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Are published posts organized in categories?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Posts_Have_Categories extends Diagnostic_Base
{
	protected static $slug = 'posts-have-categories';
	protected static $title = 'Posts Have Categories';
	protected static $description = 'Are published posts organized in categories?';
	protected static $category = 'Content Publishing';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array
	{
		// Get all published posts
		$posts = get_posts([
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids'
		]);

		if (empty($posts)) {
			return null; // No posts yet
		}

		// Check how many posts have at least one category
		$posts_with_categories = 0;
		foreach ($posts as $post_id) {
			$categories = get_the_category($post_id);
			if (! empty($categories)) {
				$posts_with_categories++;
			}
		}

		$percentage = ($posts_with_categories / count($posts)) * 100;

		if ($percentage < 50) {
			return Diagnostic_Lean_Checks::build_finding(
				'posts-have-categories',
				'Posts Not Categorized',
				sprintf('Only %.0f%% of your published posts are categorized. Consider organizing them into categories for better navigation and SEO.', $percentage),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

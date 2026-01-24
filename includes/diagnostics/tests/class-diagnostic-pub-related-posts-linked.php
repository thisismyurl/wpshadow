<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Related Posts Linked
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Are posts linking to related content?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Related_Posts_Linked extends Diagnostic_Base
{
	protected static $slug = 'pub-related-posts-linked';
	protected static $title = 'Related Posts Linked';
	protected static $description = 'Are posts linking to related content?';
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
		// Check if related posts plugin is active
		$related_plugins = [
			'related-posts',
			'yet-another-related-posts-plugin',
			'contextual-related-posts',
			'jetpack'
		];

		$has_related = false;
		foreach ($related_plugins as $plugin) {
			if (
				is_plugin_active($plugin . '/' . $plugin . '.php') ||
				is_plugin_active($plugin)
			) {
				$has_related = true;
				break;
			}
		}

		if (! $has_related) {
			// Check if site has published posts
			$posts = get_posts([
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 5
			]);

			if (! empty($posts)) {
				return Diagnostic_Lean_Checks::build_finding(
					'pub-related-posts-linked',
					'No Related Posts Plugin Found',
					'Consider enabling a related posts plugin to improve user engagement and SEO.',
					'Content Publishing',
					'low',
					'informational'
				);
			}
		}

		return null;
	}
}

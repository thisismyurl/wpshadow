<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Content Update Date Recent
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Are published posts being updated regularly?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 3 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Update_Date_Recent extends Diagnostic_Base
{
	protected static $slug = 'pub-update-date-recent';
	protected static $title = 'Content Update Date Recent';
	protected static $description = 'Are published posts being updated regularly?';
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
		// Check if posts have been updated recently (within 90 days)
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$posts = get_posts($args);

		if (empty($posts)) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-update-date-recent',
				'No Published Posts Found',
				'There are no published posts on this site.',
				'Content Publishing',
				'low',
				'informational'
			);
		}

		$ninety_days_ago = strtotime('-90 days');
		$recent_count = 0;

		foreach ($posts as $post_id) {
			$modified = get_post_modified_time('U', false, $post_id);
			if ($modified > $ninety_days_ago) {
				$recent_count++;
			}
		}

		$percentage = ($recent_count / count($posts)) * 100;

		if ($percentage < 20) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-update-date-recent',
				'Posts Not Updated Regularly',
				sprintf('Only %.0f%% of published posts have been updated in the last 90 days. Consider refreshing older content.', $percentage),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

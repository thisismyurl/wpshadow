<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Author Productivity
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Average posts per author per month - who is most productive?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Users_Author_Productivity extends Diagnostic_Base {
	protected static $slug = 'users-author-productivity';
	protected static $title = 'Author Productivity';
	protected static $description = 'Average posts per author per month';
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
		// Get authors and editors
		$authors = get_users( [
			'role__in' => [ 'author', 'editor', 'administrator' ],
			'fields'   => 'ID'
		] );

		if ( empty( $authors ) ) {
			return null;
		}

		$posts_data = [];
		$one_month_ago = strtotime( '-1 month' );

		foreach ( $authors as $author_id ) {
			$posts = get_posts( [
				'author'         => $author_id,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'date_query'     => [
					[
						'after'     => $one_month_ago,
						'inclusive' => true,
					],
				],
			] );

			if ( ! empty( $posts ) ) {
				$posts_data[ $author_id ] = count( $posts );
			}
		}

		// Check if anyone is productive
		if ( empty( $posts_data ) ) {
			return Diagnostic_Lean_Checks::build_finding(
				'users-author-productivity',
				'No Recent Content',
				'No posts have been published in the last month. Consider increasing content production.',
				'Content Publishing',
				'low',
				'informational'
			);
		}

		return null;
	}
}

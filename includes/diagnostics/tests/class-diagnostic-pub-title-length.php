<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Post Title Length
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Are post titles appropriate length for SEO?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Continuous batch implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Title_Length extends Diagnostic_Base {
	protected static $slug         = 'pub-title-length';
	protected static $title        = 'Post Title Length';
	protected static $description  = 'Are post titles appropriate length for SEO?';
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
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$issues = 0;
		foreach ( $posts as $post ) {
			$title_length = strlen( $post->post_title );
			// SEO best practice: 50-60 characters
			if ( $title_length < 30 || $title_length > 60 ) {
				++$issues;
			}
		}

		$issue_percentage = ( $issues / count( $posts ) ) * 100;

		if ( $issue_percentage > 30 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-title-length',
				'Post Titles Not SEO-Optimized',
				sprintf( '%.0f%% of recent posts have titles outside the SEO-recommended 50-60 character range.', $issue_percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

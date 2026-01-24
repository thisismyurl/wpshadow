<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Content Has Internal Links
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7
 *
 * Test Description:
 * Do posts link to other relevant content on the site?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 5+ implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Pub_Internal_Links_Anchor_Text extends Diagnostic_Base {
	protected static $slug = 'pub-internal-links-anchor-text';
	protected static $title = 'Content Has Internal Links';
	protected static $description = 'Do posts link to other relevant content on the site?';
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
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC'
		] );

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_internal_links = 0;
		$site_url = home_url();

		foreach ( $posts as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );
			// Look for internal links (href containing the site domain but not external)
			if ( preg_match( '/href=["\']' . preg_quote( $site_url, '/' ) . '[^"\']*["\']/', $content ) ) {
				$posts_with_internal_links++;
			}
		}

		$percentage = ( $posts_with_internal_links / count( $posts ) ) * 100;

		if ( $percentage < 30 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-internal-links-anchor-text',
				'Low Internal Linking',
				sprintf( 'Only %.0f%% of recent posts have internal links. Internal linking helps SEO and keeps readers on your site longer.', $percentage ),
				'Content Publishing',
				'low',
				'low'
			);
		}

		return null;
	}
}

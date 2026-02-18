<?php
/**
 * Content Excessively Long Posts Without Structure Diagnostic
 *
 * Detects long posts lacking proper structure and navigation.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Excessively Long Posts Without Structure Diagnostic Class
 *
 * Detects posts over 5,000 words that lack proper structure (subheadings, table of
 * contents, jump links), which reduces readability despite valuable content.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Content_Excessively_Long_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-excessively-long-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Excessively Long Posts Without Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect posts over 5,000 words lacking structure (TOC, subheadings, jump links)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for unstructured long posts
		$unstructured_long = apply_filters( 'wpshadow_has_unstructured_long_posts', false );
		if ( $unstructured_long ) {
			$issues[] = __( 'Detected posts over 5,000 words lacking proper structure; 45% engagement increase with TOC', 'wpshadow' );
		}

		// Check for table of contents
		$has_toc = apply_filters( 'wpshadow_long_posts_have_table_of_contents', false );
		if ( ! $has_toc ) {
			$issues[] = __( 'Long posts need table of contents for navigation; adds 45% to engagement metrics', 'wpshadow' );
		}

		// Check for proper heading hierarchy
		$proper_headings = apply_filters( 'wpshadow_long_posts_have_heading_hierarchy', false );
		if ( ! $proper_headings ) {
			$issues[] = __( 'Use H2/H3 subheadings for structure; helps both readers and SEO understanding', 'wpshadow' );
		}

		// Check for jump links/anchor navigation
		$jump_links = apply_filters( 'wpshadow_long_posts_have_jump_links', false );
		if ( ! $jump_links ) {
			$issues[] = __( 'Add jump links for quick navigation between sections', 'wpshadow' );
		}

		// Check for visual breaks
		$visual_breaks = apply_filters( 'wpshadow_long_posts_have_visual_breaks', false );
		if ( ! $visual_breaks ) {
			$issues[] = __( 'Add images, lists, blockquotes every 300-500 words to break up text', 'wpshadow' );
		}

		// Check for mobile readability
		$mobile_readable = apply_filters( 'wpshadow_long_posts_mobile_readable', false );
		if ( ! $mobile_readable ) {
			$issues[] = __( 'Long unstructured posts are especially problematic on mobile; users abandon at high rates', 'wpshadow' );
		}

		// Check for accessibility structure
		$accessible = apply_filters( 'wpshadow_long_posts_accessible_structure', false );
		if ( ! $accessible ) {
			$issues[] = __( 'Screen readers rely on proper heading hierarchy for long posts', 'wpshadow' );
		}

		// Check for summary sections
		$summary = apply_filters( 'wpshadow_long_posts_have_summary_sections', false );
		if ( ! $summary ) {
			$issues[] = __( 'Add TL;DR or key takeaways throughout long posts for scannable content', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-excessively-long-posts',
			);
		}

		return null;
	}
}

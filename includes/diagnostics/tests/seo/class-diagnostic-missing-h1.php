<?php
/**
 * Missing H1 Tag Diagnostic
 *
 * Tests whether pages are missing H1 tags entirely. Every page needs exactly
 * one H1 tag to define the primary topic for search engines and accessibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_H1 Class
 *
 * Detects pages without H1 tags which is a critical SEO issue. Every page
 * should have exactly one H1 defining the primary topic.
 *
 * @since 1.5003.1200
 */
class Diagnostic_Missing_H1 extends Diagnostic_Base {

	protected static $slug = 'missing-h1';
	protected static $title = 'Missing H1 Tag';
	protected static $description = 'Tests whether pages are missing H1 tags';
	protected static $family = 'structure';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();
		$problem_pages   = array();

		// Get sample of recent posts and pages.
		$content = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$pages_checked  = 0;
		$pages_missing_h1 = 0;

		foreach ( $content as $post ) {
			++$pages_checked;
			$post_content = $post->post_content;
			
			// Check for H1 tags in content.
			preg_match( '/<h1[^>]*>/i', $post_content, $h1_match );

			// Note: Most themes add H1 via template, so this checks content only.
			// A more thorough check would need to render the page.
			if ( empty( $h1_match ) ) {
				// Check if title would be used as H1 (common theme pattern).
				if ( strpos( $post_content, '<h2' ) === false && strpos( $post_content, '<h3' ) === false ) {
					// No headings at all in content.
					++$pages_missing_h1;
					$problem_pages[] = array(
						'title' => $post->post_title,
						'url'   => get_permalink( $post ),
					);
				}
			}
		}

		// Score based on percentage with issues.
		if ( $pages_checked > 0 ) {
			$issue_percentage = ( $pages_missing_h1 / $pages_checked ) * 100;

			if ( $issue_percentage === 0 ) {
				$score = 3;
				$score_details[] = __( '✓ All pages appear to have H1 tags', 'wpshadow' );
			} elseif ( $issue_percentage < 15 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ %d page(s) may be missing H1 tags', 'wpshadow' ), $pages_missing_h1 );
				$recommendations[] = __( 'Verify these pages and ensure theme adds H1 for post titles', 'wpshadow' );
			} else {
				$score = 0;
				$score_details[]   = sprintf( __( '✗ %d%% of pages missing H1 tags (%d of %d checked)', 'wpshadow' ), round( $issue_percentage ), $pages_missing_h1, $pages_checked );
				$recommendations[] = __( 'Add H1 tags to all pages - critical for SEO and accessibility', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 60;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Missing H1 score: %d%%. Every page must have exactly one H1 tag defining the primary topic. Missing H1 tags can reduce rankings by 30-50%% and fail WCAG accessibility requirements. Search engines use H1 as the strongest on-page ranking signal.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/h1-tag-importance',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $problem_pages, 0, 5 ),
			'impact'           => __( 'H1 tags are critical for SEO, defining page topic for search engines, and accessibility for screen reader users.', 'wpshadow' ),
		);
	}
}

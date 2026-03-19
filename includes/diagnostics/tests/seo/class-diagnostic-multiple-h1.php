<?php
/**
 * Multiple H1 Tags Diagnostic
 *
 * Tests whether pages have multiple H1 tags which can confuse search engines
 * about the primary topic of the page. Best practice is one H1 per page.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multiple_H1 Class
 *
 * Detects pages with 2 or more H1 tags which confuses search engine understanding
 * of page hierarchy and primary topic.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multiple_H1 extends Diagnostic_Base {

	protected static $slug = 'multiple-h1';
	protected static $title = 'Multiple H1 Tags';
	protected static $description = 'Tests whether pages have multiple H1 tags';
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
		$pages_with_multiple_h1 = 0;

		foreach ( $content as $post ) {
			++$pages_checked;
			$post_content = $post->post_content;
			
			// Count H1 tags in content.
			preg_match_all( '/<h1[^>]*>/i', $post_content, $h1_matches );
			$h1_count = count( $h1_matches[0] );

			if ( $h1_count >= 2 ) {
				++$pages_with_multiple_h1;
				$problem_pages[] = array(
					'title' => $post->post_title,
					'url'   => get_permalink( $post ),
					'count' => $h1_count,
				);
			}
		}

		// Score based on percentage with issues.
		if ( $pages_checked > 0 ) {
			$issue_percentage = ( $pages_with_multiple_h1 / $pages_checked ) * 100;

			if ( $issue_percentage === 0 ) {
				$score = 3;
				$score_details[] = __( '✓ No multiple H1 issues detected', 'wpshadow' );
			} elseif ( $issue_percentage < 20 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ %d%% of pages have multiple H1 tags', 'wpshadow' ), round( $issue_percentage ) );
				$recommendations[] = __( 'Review and fix pages with multiple H1 tags', 'wpshadow' );
			} else {
				$score = 1;
				$score_details[]   = sprintf( __( '✗ %d%% of pages have multiple H1 tags (%d of %d checked)', 'wpshadow' ), round( $issue_percentage ), $pages_with_multiple_h1, $pages_checked );
				$recommendations[] = __( 'Use only one H1 per page - your theme likely adds one automatically', 'wpshadow' );
			}
		}

		// Check if theme follows best practices.
		$template = get_template();
		$score_details[] = sprintf( __( 'Theme: %s', 'wpshadow' ), $template );

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 40;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Multiple H1 score: %d%%. Pages should have exactly one H1 tag that clearly defines the primary topic. Multiple H1 tags confuse search engines about page hierarchy and can reduce rankings by 15-25%%. Most themes add the H1 automatically for post titles.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/heading-structure',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $problem_pages, 0, 5 ),
			'impact'           => __( 'Clean heading hierarchy helps search engines understand content structure and improves accessibility for screen readers.', 'wpshadow' ),
		);
	}
}

<?php
/**
 * Custom Post Type Content Health Diagnostic
 *
 * Analyzes the quality and completeness of CPT content,
 * identifying posts with missing fields or suboptimal data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Content_Health Class
 *
 * Checks content quality metrics across all custom post types.
 *
 * @since 0.6093.1200
 */
class Diagnostic_CPT_Content_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-content-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Content Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes content quality and completeness across custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * CPT slugs to analyze
	 *
	 * @var array
	 */
	private static $cpt_slugs = array(
		'testimonial',
		'team_member',
		'portfolio_item',
		'wps_event',
		'resource',
		'case_study',
		'service',
		'location',
		'documentation',
		'wps_product',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes content across all CPTs for quality issues.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$total_posts = 0;
		$problematic_posts = 0;

		foreach ( self::$cpt_slugs as $cpt_slug ) {
			if ( ! post_type_exists( $cpt_slug ) ) {
				continue;
			}

			// Query posts for this CPT.
			$query = new \WP_Query( array(
				'post_type'      => $cpt_slug,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'no_found_rows'  => false,
			) );

			$total_posts += $query->found_posts;

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID();

					$post_issues = self::analyze_post_quality( $post_id, $cpt_slug );
					if ( ! empty( $post_issues ) ) {
						++$problematic_posts;
					}
				}
				wp_reset_postdata();
			}
		}

		// Calculate percentage of problematic posts.
		if ( $total_posts > 0 ) {
			$problem_percentage = ( $problematic_posts / $total_posts ) * 100;

			// Report if more than 10% have issues.
			if ( $problem_percentage > 10 ) {
				$description = sprintf(
					/* translators: 1: number of posts, 2: percentage */
					__( '%1$d out of %2$d published posts (%3$d%%) have content quality issues. ', 'wpshadow' ),
					$problematic_posts,
					$total_posts,
					round( $problem_percentage )
				);

				$description .= __( 'Common issues include missing featured images, short content, missing excerpts, and incomplete custom fields. Improving content quality can enhance user engagement and SEO performance.', 'wpshadow' );

				$severity = 'low';
				$threat_level = 25;

				if ( $problem_percentage > 30 ) {
					$severity = 'medium';
					$threat_level = 50;
				}

				if ( $problem_percentage > 50 ) {
					$severity = 'high';
					$threat_level = 70;
				}

				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => $description,
					'severity'     => $severity,
					'threat_level' => $threat_level,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/improving-content-quality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'academy_link' => 'https://wpshadow.com/academy/content-best-practices',
				);
			}
		}

		return null; // Content health is acceptable.
	}

	/**
	 * Analyze individual post quality
	 *
	 * @since 0.6093.1200
	 * @param  int    $post_id  Post ID to analyze.
	 * @param  string $cpt_slug CPT slug.
	 * @return array Array of issues found.
	 */
	private static function analyze_post_quality( $post_id, $cpt_slug ) {
		$issues = array();
		$post = get_post( $post_id );

		// Check featured image.
		if ( ! has_post_thumbnail( $post_id ) ) {
			$issues[] = 'missing_featured_image';
		}

		// Check content length (should be at least 100 words).
		$content = wp_strip_all_tags( $post->post_content );
		$word_count = str_word_count( $content );
		if ( $word_count < 100 ) {
			$issues[] = 'short_content';
		}

		// Check excerpt.
		if ( empty( $post->post_excerpt ) ) {
			$issues[] = 'missing_excerpt';
		}

		// Check taxonomies.
		$taxonomies = get_object_taxonomies( $cpt_slug );
		$has_terms = false;
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$has_terms = true;
				break;
			}
		}
		if ( ! $has_terms ) {
			$issues[] = 'no_taxonomies';
		}

		return $issues;
	}

	/**
	 * Get content health score (0-100)
	 *
	 * @since 0.6093.1200
	 * @return int Health score.
	 */
	public static function get_health_score() {
		$total_posts = 0;
		$problematic_posts = 0;

		foreach ( self::$cpt_slugs as $cpt_slug ) {
			if ( ! post_type_exists( $cpt_slug ) ) {
				continue;
			}

			$query = new \WP_Query( array(
				'post_type'      => $cpt_slug,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'no_found_rows'  => false,
			) );

			$total_posts += $query->found_posts;

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_issues = self::analyze_post_quality( get_the_ID(), $cpt_slug );
					if ( ! empty( $post_issues ) ) {
						++$problematic_posts;
					}
				}
				wp_reset_postdata();
			}
		}

		if ( 0 === $total_posts ) {
			return 100; // No posts to analyze.
		}

		return max( 0, 100 - ( ( $problematic_posts / $total_posts ) * 100 ) );
	}
}

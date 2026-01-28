<?php
/**
 * Meta Description Length Diagnostic
 *
 * Measures meta description length. Too short wastes SERP real estate;
 * too long gets truncated in search results (typically at ~160 chars).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1650
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Meta_Description_Length Class
 *
 * Analyzes meta descriptions across all published pages to ensure
 * they fall within the optimal 120-160 character range for SERP display.
 *
 * @since 1.6028.1650
 */
class Diagnostic_Meta_Description_Length extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-description-length';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Description Length Outside 120-160 Characters';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures meta description length for optimal SERP display';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1650
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_meta_descriptions();

		if ( empty( $analysis['problematic_count'] ) ) {
			return null; // All meta descriptions are optimal.
		}

		$total                = $analysis['total_pages'];
		$problematic          = $analysis['problematic_count'];
		$problematic_percentage = ( $problematic / max( $total, 1 ) ) * 100;

		// Determine severity based on percentage.
		if ( $problematic_percentage > 50 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $problematic_percentage > 25 ) {
			$severity     = 'low';
			$threat_level = 35;
		} else {
			$severity     = 'info';
			$threat_level = 20;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage of problematic descriptions, 2: number of pages */
				__( '%1$s%% of pages (%2$d) have meta descriptions outside optimal 120-160 character range', 'wpshadow' ),
				number_format( $problematic_percentage, 1 ),
				$problematic
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/meta-description-length',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'    => $problematic,
				'total_pages'       => $total,
				'too_short'         => $analysis['too_short'],
				'too_long'          => $analysis['too_long'],
				'missing'           => $analysis['missing'],
				'optimal_range'     => '120-160 characters',
				'acceptable_range'  => '90-180 characters',
				'recommended'       => __( 'All pages should have 120-160 character descriptions', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Review pages with missing descriptions', 'wpshadow' ),
					__( 'Expand descriptions that are too short', 'wpshadow' ),
					__( 'Trim descriptions that are too long', 'wpshadow' ),
					__( 'Include target keywords naturally', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Meta descriptions are your sales pitch in search results. Too short wastes valuable SERP real estate and fails to entice clicks. Too long gets truncated (usually at ~160 characters), cutting off your message. Optimal length maximizes visibility and click-through rate.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Lower CTR: Poor descriptions reduce clicks from search results', 'wpshadow' ),
					__( 'Wasted Space: Short descriptions don\'t utilize full SERP display', 'wpshadow' ),
					__( 'Truncated Message: Long descriptions get cut off mid-sentence', 'wpshadow' ),
					__( 'Missing Value Prop: Users can\'t see why they should click', 'wpshadow' ),
				),
				'breakdown'     => array(
					'too_short' => $analysis['too_short'],
					'too_long'  => $analysis['too_long'],
					'missing'   => $analysis['missing'],
					'optimal'   => $total - $problematic,
				),
				'examples'      => $analysis['examples'],
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Manual Description Optimization', 'wpshadow' ),
						'description' => __( 'Review and rewrite descriptions page by page', 'wpshadow' ),
						'steps'       => array(
							__( 'Export list of pages with problematic descriptions', 'wpshadow' ),
							__( 'Write new descriptions between 120-160 characters', 'wpshadow' ),
							__( 'Include primary keyword and compelling call-to-action', 'wpshadow' ),
							__( 'Update via WordPress editor or SEO plugin', 'wpshadow' ),
							__( 'Preview in Google SERP simulator', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'SEO Plugin Bulk Editor', 'wpshadow' ),
						'description' => __( 'Use Yoast or RankMath bulk editor for faster updates', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO Premium or RankMath Pro', 'wpshadow' ),
							__( 'Navigate to bulk editor (SEO → Tools → Bulk Editor)', 'wpshadow' ),
							__( 'Filter by missing or problematic descriptions', 'wpshadow' ),
							__( 'Edit descriptions inline with character counter', 'wpshadow' ),
							__( 'Save all changes in batch', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'AI-Assisted Description Generation', 'wpshadow' ),
						'description' => __( 'Use ChatGPT or similar to generate descriptions in bulk', 'wpshadow' ),
						'steps'       => array(
							__( 'Export pages with content and target keywords', 'wpshadow' ),
							__( 'Create prompt: "Write 150-char meta description for [title/content]"', 'wpshadow' ),
							__( 'Generate descriptions via AI tool', 'wpshadow' ),
							__( 'Review for accuracy and brand voice', 'wpshadow' ),
							__( 'Import back into WordPress via CSV or API', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Target 150 characters as sweet spot (middle of 120-160 range)', 'wpshadow' ),
					__( 'Include primary keyword naturally', 'wpshadow' ),
					__( 'Add compelling call-to-action', 'wpshadow' ),
					__( 'Match search intent of target query', 'wpshadow' ),
					__( 'Make each description unique (no duplicates)', 'wpshadow' ),
					__( 'Front-load important information', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run this diagnostic after making changes', 'wpshadow' ),
						__( 'Test descriptions in Google SERP preview tool', 'wpshadow' ),
						__( 'Monitor CTR changes in Search Console', 'wpshadow' ),
						__( 'A/B test different description styles', 'wpshadow' ),
					),
					'expected_result' => __( '>90% of pages have descriptions between 120-160 characters', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze meta descriptions across all published pages.
	 *
	 * @since  1.6028.1650
	 * @return array Analysis results with counts and examples.
	 */
	private static function analyze_meta_descriptions() {
		$result = array(
			'total_pages'        => 0,
			'problematic_count'  => 0,
			'too_short'          => 0,
			'too_long'           => 0,
			'missing'            => 0,
			'examples'           => array(),
		);

		// Get all published posts and pages.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return $result;
		}

		$result['total_pages'] = count( $posts );
		$example_limit         = 5;
		$example_count         = 0;

		foreach ( $posts as $post_id ) {
			$description = self::get_meta_description( $post_id );
			$length      = strlen( $description );

			// Categorize by length.
			if ( empty( $description ) ) {
				$result['missing']++;
				$result['problematic_count']++;
				if ( $example_count < $example_limit ) {
					$result['examples'][] = array(
						'url'         => get_permalink( $post_id ),
						'title'       => get_the_title( $post_id ),
						'issue'       => 'missing',
						'length'      => 0,
						'description' => '',
					);
					$example_count++;
				}
			} elseif ( $length < 90 ) {
				$result['too_short']++;
				$result['problematic_count']++;
				if ( $example_count < $example_limit ) {
					$result['examples'][] = array(
						'url'         => get_permalink( $post_id ),
						'title'       => get_the_title( $post_id ),
						'issue'       => 'too_short',
						'length'      => $length,
						'description' => $description,
					);
					$example_count++;
				}
			} elseif ( $length > 180 ) {
				$result['too_long']++;
				$result['problematic_count']++;
				if ( $example_count < $example_limit ) {
					$result['examples'][] = array(
						'url'         => get_permalink( $post_id ),
						'title'       => get_the_title( $post_id ),
						'issue'       => 'too_long',
						'length'      => $length,
						'description' => substr( $description, 0, 100 ) . '...',
					);
					$example_count++;
				}
			}
		}

		return $result;
	}

	/**
	 * Get meta description for a specific post.
	 *
	 * Checks SEO plugin meta first, falls back to excerpt or auto-generation.
	 *
	 * @since  1.6028.1650
	 * @param  int $post_id Post ID.
	 * @return string Meta description value.
	 */
	private static function get_meta_description( $post_id ) {
		// Check Yoast SEO.
		$yoast_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
		if ( ! empty( $yoast_desc ) ) {
			return $yoast_desc;
		}

		// Check RankMath.
		$rankmath_desc = get_post_meta( $post_id, 'rank_math_description', true );
		if ( ! empty( $rankmath_desc ) ) {
			return $rankmath_desc;
		}

		// Fallback to excerpt (WordPress may auto-generate in <head>).
		$post = get_post( $post_id );
		if ( $post && ! empty( $post->post_excerpt ) ) {
			return $post->post_excerpt;
		}

		return '';
	}
}

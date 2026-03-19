<?php
/**
 * Case Studies Diagnostic
 *
 * Checks whether case studies or success stories are published.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Case Studies Diagnostic Class
 *
 * Verifies that case studies or success stories are available to build trust.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Case_Studies extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'case-studies';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Case Studies or Success Stories';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether case studies and results-based stories are published';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'trust-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for dedicated case study content (50 points).
		$case_study_pages = self::find_pages_by_keywords(
			array(
				'case study',
				'success story',
				'client story',
				'results',
			)
		);

		if ( count( $case_study_pages ) > 0 ) {
			$earned_points             += 50;
			$stats['case_study_pages'] = implode( ', ', $case_study_pages );
		} else {
			$issues[] = __( 'No case studies or success stories detected', 'wpshadow' );
		}

		// Check for case study post types (30 points).
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			if ( false !== strpos( $post_type, 'case' ) || false !== strpos( $post_type, 'story' ) ) {
				$earned_points += 30;
				$stats['case_study_post_type'] = $post_type;
				break;
			}
		}

		if ( empty( $stats['case_study_post_type'] ) ) {
			$warnings[] = __( 'No dedicated case study post type detected', 'wpshadow' );
		}

		// Check for testimonials or review plugins (20 points).
		$testimonial_plugins = array(
			'site-reviews/site-reviews.php'       => 'Site Reviews',
			'strong-testimonials/strong-testimonials.php' => 'Strong Testimonials',
			'wp-customer-reviews/wp-customer-reviews.php' => 'WP Customer Reviews',
		);

		$active_testimonials = array();
		foreach ( $testimonial_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_testimonials[] = $plugin_name;
				$earned_points        += 10;
			}
		}

		if ( count( $active_testimonials ) > 0 ) {
			$stats['testimonial_tools'] = implode( ', ', $active_testimonials );
		} else {
			$warnings[] = __( 'No testimonial or review tools detected', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your case study coverage scored %s. Case studies are proof that your work delivers real results. Without them, prospects must take your promises on faith. A few clear before-and-after stories can dramatically improve trust and conversions.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/case-studies',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}

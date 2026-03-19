<?php
/**
 * Competitive Comparison Diagnostic
 *
 * Checks whether a competitive comparison page or content exists.
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
 * Competitive Comparison Diagnostic Class
 *
 * Verifies that visitors can see a clear feature or benefit comparison.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Competitive_Comparison extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'competitive-comparison';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Competitive Feature or Benefit Comparison';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for a comparison page or competitive positioning content';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'competitive-analysis';

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

		// Check for comparison pages (45 points).
		$comparison_pages = self::find_pages_by_keywords(
			array(
				'comparison',
				'compare',
				'vs',
				'versus',
				'why choose',
				'why us',
			)
		);

		if ( count( $comparison_pages ) > 0 ) {
			$earned_points             += 45;
			$stats['comparison_pages'] = implode( ', ', $comparison_pages );
		} else {
			$issues[] = __( 'No comparison or "why choose us" page detected', 'wpshadow' );
		}

		// Check for pricing or plan tables (30 points).
		$pricing_plugins = array(
			'woocommerce/woocommerce.php'                      => 'WooCommerce',
			'wp-pricing-table/wp-pricing-table.php'            => 'WP Pricing Table',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
		);

		$active_pricing = array();
		foreach ( $pricing_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_pricing[] = $plugin_name;
				$earned_points  += 15;
			}
		}

		if ( count( $active_pricing ) > 0 ) {
			$stats['pricing_platforms'] = implode( ', ', $active_pricing );
		} else {
			$warnings[] = __( 'No pricing or plan comparison tools detected', 'wpshadow' );
		}

		// Check for testimonials or switching stories (25 points).
		$testimonial_pages = self::find_pages_by_keywords(
			array(
				'testimonials',
				'customer stories',
				'switching',
				'reviews',
			)
		);

		if ( count( $testimonial_pages ) > 0 ) {
			$earned_points             += 25;
			$stats['testimonial_pages'] = implode( ', ', $testimonial_pages );
		} else {
			$warnings[] = __( 'No customer switching stories or testimonials detected', 'wpshadow' );
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
					__( 'Your competitive comparison scored %s. When customers compare you to competitors, a clear side-by-side comparison helps them decide. Without it, they must guess what makes you different. A simple "Why choose us" or comparison page can guide the decision in your favor.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/competitive-comparison',
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

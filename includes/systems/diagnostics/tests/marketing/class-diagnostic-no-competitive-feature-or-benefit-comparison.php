<?php
/**
 * No Competitive Feature or Benefit Comparison Diagnostic
 *
 * Checks whether the site provides a clear comparison against alternatives.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Competitive Feature Comparison Diagnostic
 *
 * Detects when visitors cannot easily compare your offering with alternatives.
 * Clear comparisons reduce decision friction and improve conversions.
 *
 * @since 1.6035.1430
 */
class Diagnostic_No_Competitive_Feature_Or_Benefit_Comparison extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-competitive-feature-comparison';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Competitive Feature Comparison Provided';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether visitors can see a clear comparison of features or benefits';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$comparison_pages = self::count_comparison_pages();

		if ( 0 === $comparison_pages ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Visitors can compare your service in their heads, but not on your site. A simple comparison page reduces decision stress and helps people choose confidently. Consider a clear, fair comparison that highlights your strengths and explains who you are best for.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/competitive-comparison',
				'details'      => array(
					'comparison_pages_found' => $comparison_pages,
					'recommendation'         => __( 'Create a comparison page that explains key differences, ideal use cases, and trade-offs in plain language.', 'wpshadow' ),
					'comparison_elements'    => self::get_comparison_elements(),
				),
			);
		}

		return null;
	}

	/**
	 * Count pages or posts that look like competitive comparisons.
	 *
	 * @since  1.6035.1430
	 * @return int Number of comparison pages found.
	 */
	private static function count_comparison_pages(): int {
		$keywords = array(
			'comparison',
			'compare',
			'vs',
			'versus',
			'alternatives',
			'why choose',
		);

		return self::count_posts_by_keywords( $keywords );
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since  1.6035.1430
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Get recommended comparison elements.
	 *
	 * @since  1.6035.1430
	 * @return array Comparison elements.
	 */
	private static function get_comparison_elements(): array {
		return array(
			__( 'Who each option is best for (clear use cases)', 'wpshadow' ),
			__( 'Feature or benefit differences in plain language', 'wpshadow' ),
			__( 'Transparent pricing differences', 'wpshadow' ),
			__( 'Honest trade-offs and limitations', 'wpshadow' ),
			__( 'A short summary to help decide quickly', 'wpshadow' ),
		);
	}
}

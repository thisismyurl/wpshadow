<?php
/**
 * Diagnostic: Seasonal Content Pattern Issues
 *
 * Detects unbalanced seasonal content distribution. Publishing 80% of content
 * in one quarter neglects year-round SEO opportunities.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4382
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seasonal Content Pattern Diagnostic
 *
 * Analyzes content distribution across quarters to detect seasonal imbalance.
 * Balanced publishing throughout the year maximizes SEO opportunities.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Seasonal_Pattern_Issues extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'seasonal-pattern-issues';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Seasonal Content Pattern Issues';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unbalanced content distribution across seasons that limits year-round SEO';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for seasonal content imbalance.
	 *
	 * Analyzes last 12 months to check if content is concentrated in specific
	 * quarters. Ideally, content should be distributed relatively evenly.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get posts from last 12 months.
		$one_year_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-365 days' ) );

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT QUARTER(post_date) as quarter, COUNT(*) as count
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s
				GROUP BY QUARTER(post_date)",
				$one_year_ago
			)
		);

		if ( empty( $posts ) || count( $posts ) < 2 ) {
			// Not enough data or posts only in one quarter.
			return null;
		}

		// Calculate total posts and distribution.
		$total           = 0;
		$quarter_counts  = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0 );

		foreach ( $posts as $post ) {
			$quarter_counts[ (int) $post->quarter ] = (int) $post->count;
			$total                                 += (int) $post->count;
		}

		if ( $total < 10 ) {
			// Not enough posts to determine pattern.
			return null;
		}

		// Check if any quarter has 80%+ of content.
		$max_count       = max( $quarter_counts );
		$max_percentage  = ( $max_count / $total ) * 100;
		$quarter_with_max = array_search( $max_count, $quarter_counts, true );

		if ( $max_percentage < 80 ) {
			// Distribution is acceptable.
			return null;
		}

		$threat_level = 55; // Medium severity.

		if ( $max_percentage >= 90 ) {
			$threat_level = 65; // Very concentrated.
		}

		$quarter_names = array(
			1 => __( 'Q1 (Jan-Mar)', 'wpshadow' ),
			2 => __( 'Q2 (Apr-Jun)', 'wpshadow' ),
			3 => __( 'Q3 (Jul-Sep)', 'wpshadow' ),
			4 => __( 'Q4 (Oct-Dec)', 'wpshadow' ),
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: quarter name, 3: post count, 4: total posts */
				__(
					'Content is heavily concentrated in %2$s (%.1f%% - %3$d of %4$d posts). Balanced year-round publishing improves SEO performance and reduces seasonal traffic fluctuations.',
					'wpshadow'
				),
				$max_percentage,
				$quarter_names[ $quarter_with_max ],
				$max_count,
				$total
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/seasonal-content-patterns?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}

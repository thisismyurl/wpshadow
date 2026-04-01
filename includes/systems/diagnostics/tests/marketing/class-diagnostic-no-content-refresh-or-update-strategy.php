<?php
/**
 * No Content Refresh or Update Strategy Diagnostic
 *
 * Checks if content refresh/update strategy is in place.
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
 * Content Refresh/Update Strategy Diagnostic
 *
 * Updating old content can increase organic traffic by 111% (HubSpot).
 * Outdated content hurts SEO rankings and user trust.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Refresh_Or_Update_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-refresh-update-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Content Refresh or Update Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content refresh or update strategy is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$old_content = self::count_old_content();

		if ( $old_content > 20 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of old posts */
					__( 'You have %d posts over 18 months old with no updates. Outdated content kills SEO rankings and user trust. Refreshing content increases organic traffic by 111%% (HubSpot). Strategy: 1) Audit content quarterly (last updated, traffic, rankings), 2) Prioritize top-performing pages first, 3) Update statistics and facts, 4) Add new sections/examples, 5) Improve formatting/readability, 6) Update images/screenshots, 7) Republish with new date. Fresh content wins.', 'wpshadow' ),
					$old_content
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/content-refresh-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => sprintf(
						/* translators: %d: number of old posts */
						__( '%d posts older than 18 months without updates', 'wpshadow' ),
						$old_content
					),
					'recommendation'      => __( 'Implement quarterly content audit and refresh schedule', 'wpshadow' ),
					'business_impact'     => __( 'Losing up to 111% potential traffic increase from outdated content', 'wpshadow' ),
					'old_post_count'      => $old_content,
					'refresh_priorities'  => self::get_refresh_priorities(),
					'update_checklist'    => self::get_update_checklist(),
				),
			);
		}

		return null;
	}

	/**
	 * Count old content (18+ months without update).
	 *
	 * @since 0.6093.1200
	 * @return int Number of old posts.
	 */
	private static function count_old_content() {
		$cutoff_date = gmdate( 'Y-m-d', strtotime( '-18 months' ) );

		$old_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'date_query'     => array(
					array(
						'column' => 'post_modified',
						'before' => $cutoff_date,
					),
				),
			)
		);

		return count( $old_posts );
	}

	/**
	 * Get content refresh priorities.
	 *
	 * @since 0.6093.1200
	 * @return array Prioritization criteria.
	 */
	private static function get_refresh_priorities() {
		return array(
			'high_traffic'    => __( 'Top 20% of pages by organic traffic (refresh first)', 'wpshadow' ),
			'ranking_drops'   => __( 'Pages that dropped in rankings recently (urgent)', 'wpshadow' ),
			'top_conversions' => __( 'Pages with highest conversion rates (revenue impact)', 'wpshadow' ),
			'seasonal'        => __( 'Seasonal content before peak season (timely)', 'wpshadow' ),
			'competitor_gaps' => __( 'Topics where competitors outrank you (opportunity)', 'wpshadow' ),
			'old_stats'       => __( 'Content with outdated statistics (credibility)', 'wpshadow' ),
		);
	}

	/**
	 * Get content update checklist.
	 *
	 * @since 0.6093.1200
	 * @return array Update checklist items.
	 */
	private static function get_update_checklist() {
		return array(
			'statistics'    => __( 'Update all statistics, data points, and facts', 'wpshadow' ),
			'examples'      => __( 'Add recent examples, case studies, or quotes', 'wpshadow' ),
			'sections'      => __( 'Add new sections for topics that didn\'t exist before', 'wpshadow' ),
			'images'        => __( 'Replace outdated screenshots or add new visuals', 'wpshadow' ),
			'links'         => __( 'Check and update all external links (remove broken)', 'wpshadow' ),
			'formatting'    => __( 'Improve readability (headings, bullets, white space)', 'wpshadow' ),
			'cta'           => __( 'Update call-to-action (offers may have changed)', 'wpshadow' ),
			'seo'           => __( 'Review and optimize title, meta description, keywords', 'wpshadow' ),
			'republish'     => __( 'Update published date to signal freshness to Google', 'wpshadow' ),
		);
	}
}

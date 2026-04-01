<?php
/**
 * Content Marketing Strategy Diagnostic
 *
 * Checks whether a content marketing and SEO strategy is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Marketing Strategy Diagnostic Class
 *
 * Verifies content publishing cadence, SEO tooling, and strategy indicators.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Marketing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'content-marketing-strategy';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Content Marketing or SEO Strategy';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content marketing and SEO strategy indicators exist';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'growth-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for published blog content (40 points).
		$published_posts = wp_count_posts( 'post' );
		$post_count      = isset( $published_posts->publish ) ? (int) $published_posts->publish : 0;

		if ( $post_count >= 10 ) {
			$earned_points += 40;
			$stats['published_posts'] = $post_count;
		} else {
			$issues[] = __( 'Low blog publishing volume (fewer than 10 posts)', 'wpshadow' );
		}

		// Check for SEO tools (35 points).
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'         => 'Yoast SEO',
			'rank-math/rank-math.php'           => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'    => 'Rank Math (alternate)',
		);

		$active_seo = array();
		foreach ( $seo_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_seo[]  = $plugin_name;
				$earned_points += 18;
			}
		}

		if ( count( $active_seo ) > 0 ) {
			$stats['seo_tools'] = implode( ', ', $active_seo );
		} else {
			$issues[] = __( 'No SEO optimization tools detected', 'wpshadow' );
		}

		// Check for editorial calendar tools (25 points).
		$calendar_plugins = array(
			'editorial-calendar/editorial-calendar.php' => 'Editorial Calendar',
			'publishpress/publishpress.php'             => 'PublishPress',
		);

		$active_calendar = array();
		foreach ( $calendar_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_calendar[] = $plugin_name;
				$earned_points    += 12;
			}
		}

		if ( count( $active_calendar ) > 0 ) {
			$stats['calendar_tools'] = implode( ', ', $active_calendar );
		} else {
			$warnings[] = __( 'No editorial calendar or content planning tools detected', 'wpshadow' );
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
					__( 'Your content marketing strategy scored %s. Content and SEO are the most reliable sources of long-term, low-cost traffic. Without a steady publishing plan and SEO tools, you miss compounding growth from search.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-marketing-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}

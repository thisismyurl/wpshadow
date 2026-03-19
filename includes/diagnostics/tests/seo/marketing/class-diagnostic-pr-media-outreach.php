<?php
/**
 * PR and Media Outreach Diagnostic
 *
 * Checks whether press kit and media outreach assets exist.
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
 * PR and Media Outreach Diagnostic Class
 *
 * Verifies press kit and media outreach indicators.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Pr_Media_Outreach extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'pr-media-outreach';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No PR or Media Outreach Strategy';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for press kits, media pages, and outreach indicators';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'brand-awareness';

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

		// Check for press/media pages (60 points).
		$press_pages = self::find_pages_by_keywords(
			array(
				'press',
				'media kit',
				'press kit',
				'newsroom',
				'media',
			)
		);

		if ( count( $press_pages ) > 0 ) {
			$earned_points        += 60;
			$stats['press_pages'] = implode( ', ', $press_pages );
		} else {
			$issues[] = __( 'No press kit or media page detected', 'wpshadow' );
		}

		// Check for announcement content (25 points).
		$announcement_pages = self::find_pages_by_keywords(
			array(
				'press release',
				'announcement',
				'news',
			)
		);

		if ( count( $announcement_pages ) > 0 ) {
			$earned_points               += 25;
			$stats['announcement_pages'] = implode( ', ', $announcement_pages );
		} else {
			$warnings[] = __( 'No press release or announcement content detected', 'wpshadow' );
		}

		// Check for social sharing tools (15 points).
		$social_plugins = array(
			'shared-counts/shared-counts.php' => 'Shared Counts',
			'social-warfare/social-warfare.php' => 'Social Warfare',
			'add-to-any/add-to-any.php' => 'AddToAny',
		);

		$active_social = array();
		foreach ( $social_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_social[] = $plugin_name;
				$earned_points  += 5;
			}
		}

		if ( count( $active_social ) > 0 ) {
			$stats['social_tools'] = implode( ', ', $active_social );
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
					__( 'Your PR and media outreach scored %s. Press coverage acts like third-party validation. A simple press kit and media page can increase credibility and unlock earned media opportunities.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pr-media-outreach',
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

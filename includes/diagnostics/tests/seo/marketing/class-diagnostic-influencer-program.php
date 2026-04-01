<?php
/**
 * Influencer Program Diagnostic
 *
 * Checks whether an influencer or brand ambassador program exists.
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
 * Influencer Program Diagnostic Class
 *
 * Verifies influencer or ambassador program indicators.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Influencer_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'influencer-program';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Influencer or Brand Ambassador Program';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether an influencer or ambassador program is present';

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

		// Check for ambassador or influencer pages (50 points).
		$ambassador_pages = self::find_pages_by_keywords(
			array(
				'influencer',
				'ambassador',
				'brand partner',
				'creator program',
			)
		);

		if ( count( $ambassador_pages ) > 0 ) {
			$earned_points            += 50;
			$stats['ambassador_pages'] = implode( ', ', $ambassador_pages );
		} else {
			$issues[] = __( 'No influencer or ambassador program page detected', 'wpshadow' );
		}

		// Check for affiliate tools (30 points).
		$affiliate_plugins = array(
			'affiliate-wp/affiliate-wp.php'          => 'AffiliateWP',
			'slicewp/slicewp.php'                    => 'SliceWP',
			'affiliates-manager/affiliates-manager.php' => 'Affiliates Manager',
		);

		$active_affiliates = array();
		foreach ( $affiliate_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_affiliates[] = $plugin_name;
				$earned_points     += 10;
			}
		}

		if ( count( $active_affiliates ) > 0 ) {
			$stats['affiliate_tools'] = implode( ', ', $active_affiliates );
		} else {
			$warnings[] = __( 'No affiliate tools detected for influencer tracking', 'wpshadow' );
		}

		// Check for social proof tools (20 points).
		$social_plugins = array(
			'smash-balloon-instagram-feed/instagram-feed.php' => 'Smash Balloon Instagram',
			'smash-balloon-social-photo-feed/smash-balloon-social-photo-feed.php' => 'Instagram Feed',
			'trustpulse/trustpulse.php' => 'TrustPulse',
		);

		$active_social = array();
		foreach ( $social_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_social[] = $plugin_name;
				$earned_points  += 8;
			}
		}

		if ( count( $active_social ) > 0 ) {
			$stats['social_tools'] = implode( ', ', $active_social );
		} else {
			$warnings[] = __( 'No social proof tools detected for influencer content', 'wpshadow' );
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
					__( 'Your influencer program scored %s. Influencer partnerships create trusted, authentic promotion. Without a program, you miss a growth channel that can build social proof quickly.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/influencer-program?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
	 * @since 0.6093.1200
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

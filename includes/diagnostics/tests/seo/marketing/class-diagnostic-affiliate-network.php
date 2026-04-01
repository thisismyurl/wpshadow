<?php
/**
 * Affiliate Network Diagnostic
 *
 * Checks whether a partnership or affiliate program is configured.
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
 * Affiliate Network Diagnostic Class
 *
 * Verifies that a strategic partnership or affiliate program exists.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Affiliate_Network extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'affiliate-network';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Strategic Partnerships or Affiliate Network';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether an affiliate or partner program is active';

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

		// Check for affiliate program plugins (50 points).
		$affiliate_plugins = array(
			'affiliate-wp/affiliate-wp.php'          => 'AffiliateWP',
			'slicewp/slicewp.php'                    => 'SliceWP',
			'affiliates-manager/affiliates-manager.php' => 'Affiliates Manager',
			'wp-affiliate-platform/wp-affiliate-platform.php' => 'WP Affiliate Platform',
		);

		$active_affiliates = array();
		foreach ( $affiliate_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_affiliates[] = $plugin_name;
				$earned_points     += 25;
			}
		}

		if ( count( $active_affiliates ) > 0 ) {
			$stats['affiliate_plugins'] = implode( ', ', $active_affiliates );
		} else {
			$issues[] = __( 'No affiliate program tools detected', 'wpshadow' );
		}

		// Check for partner or affiliate pages (30 points).
		$partner_pages = self::find_pages_by_keywords(
			array(
				'affiliate',
				'partner program',
				'partnerships',
				'referral program',
			)
		);

		if ( count( $partner_pages ) > 0 ) {
			$earned_points         += 30;
			$stats['partner_pages'] = implode( ', ', $partner_pages );
		} else {
			$warnings[] = __( 'No partner or affiliate program page detected', 'wpshadow' );
		}

		// Check for onboarding tools (20 points).
		$onboarding_plugins = array(
			'learnpress/learnpress.php' => 'LearnPress',
			'lifterlms/lifterlms.php'   => 'LifterLMS',
			'wp-courseware/wp-courseware.php' => 'WP Courseware',
		);

		$active_onboarding = array();
		foreach ( $onboarding_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_onboarding[] = $plugin_name;
				$earned_points      += 8;
			}
		}

		if ( count( $active_onboarding ) > 0 ) {
			$stats['onboarding_tools'] = implode( ', ', $active_onboarding );
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
					__( 'Your affiliate network setup scored %s. Partnerships and affiliates create scalable growth because you pay only for results. Without a program, you miss a low-risk way to expand reach.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/affiliate-network?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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

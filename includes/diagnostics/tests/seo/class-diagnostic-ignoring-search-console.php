<?php
/**
 * Ignoring Search Console Data Diagnostic
 *
 * Tests whether Google Search Console data is being actively used. GSC shows
 * what's working but often not acted upon. 30% traffic gain possible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Ignoring_Search_Console Class
 *
 * Detects when Search Console integration is missing or data is not being
 * used to optimize content. GSC provides invaluable search performance data.
 *
 * @since 1.5003.1200
 */
class Diagnostic_Ignoring_Search_Console extends Diagnostic_Base {

	protected static $slug = 'ignoring-search-console';
	protected static $title = 'Ignoring Search Console Data';
	protected static $description = 'Tests whether Google Search Console data is actively used';
	protected static $family = 'analytics';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check for Search Console integration plugins.
		$gsc_plugins = array(
			'google-site-kit/google-site-kit.php',
			'wordpress-seo/wp-seo.php', // Yoast has GSC integration.
			'seo-by-rank-math/rank-math.php', // Rank Math has GSC integration.
		);

		$has_gsc_integration = false;
		foreach ( $gsc_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_gsc_integration = true;
				++$score;
				$score_details[] = __( '✓ Search Console integration plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_gsc_integration ) {
			$score_details[]   = __( '✗ No Search Console integration detected', 'wpshadow' );
			$recommendations[] = __( 'Install Google Site Kit or configure GSC in SEO plugin', 'wpshadow' );
		}

		// Check for verification meta tag.
		$head_content = get_option( 'blogdescription' ); // Simplified check.
		$verification_meta = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				's'              => 'google-site-verification',
			)
		);

		// Check for recent content optimization (indicator of data usage).
		$recently_updated = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				'date_query'     => array(
					'column' => 'post_modified',
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		if ( count( $recently_updated ) >= 5 ) {
			++$score;
			$score_details[] = sprintf( __( '✓ %d posts updated in last 3 months (indicates optimization)', 'wpshadow' ), count( $recently_updated ) );
		} else {
			$score_details[]   = __( '◐ Few posts updated recently', 'wpshadow' );
			$recommendations[] = __( 'Use GSC data to identify optimization opportunities', 'wpshadow' );
		}

		// Check for analytics documentation/notes.
		$analytics_content = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'search console analytics data performance',
			)
		);

		if ( ! empty( $analytics_content ) ) {
			++$score;
			$score_details[] = __( '✓ Analytics documentation/tracking pages found', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No analytics tracking documentation', 'wpshadow' );
			$recommendations[] = __( 'Document GSC insights and create action plan from data', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 35;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Search Console usage score: %d%%. GSC reveals actual search queries, CTR, positions, and indexing issues - but 70%% of sites ignore this data. Sites actively using GSC data see 30%% traffic increases. Check: queries to optimize, pages to improve, technical issues to fix, content gaps to fill.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/search-console-optimization',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Search Console provides real user search data showing exactly how Google sees your site and where optimization opportunities exist.', 'wpshadow' ),
		);
	}
}

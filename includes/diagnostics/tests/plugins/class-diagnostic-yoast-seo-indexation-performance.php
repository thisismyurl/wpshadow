<?php
/**
 * Yoast Seo Indexation Performance Diagnostic
 *
 * Yoast Seo Indexation Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.693.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Indexation Performance Diagnostic Class
 *
 * @since 1.693.0000
 */
class Diagnostic_YoastSeoIndexationPerformance extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-indexation-performance';
	protected static $title = 'Yoast Seo Indexation Performance';
	protected static $description = 'Yoast Seo Indexation Performance configuration issues';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify indexables are enabled
		$indexables = get_option( 'wpseo_indexables', 0 );
		if ( ! $indexables ) {
			$issues[] = 'Indexables not enabled';
		}

		// Check 2: Check for SEO data optimization
		$optimize = get_option( 'wpseo_indexation_done', 0 );
		if ( ! $optimize ) {
			$issues[] = 'SEO data optimization not completed';
		}

		// Check 3: Verify indexing cron
		$indexing_cron = wp_next_scheduled( 'wpseo_indexation' );
		if ( ! $indexing_cron ) {
			$issues[] = 'Indexation cron not scheduled';
		}

		// Check 4: Check for analysis logging
		$analysis_logging = get_option( 'wpseo_analysis_logging', 0 );
		if ( $analysis_logging ) {
			$issues[] = 'SEO analysis logging enabled (performance impact)';
		}

		// Check 5: Verify primary category feature
		$primary_category = get_option( 'wpseo_primary_category', 0 );
		if ( ! $primary_category ) {
			$issues[] = 'Primary category feature not configured';
		}

		// Check 6: Check for sitemap cache
		$sitemap_cache = get_option( 'wpseo_sitemap_cache', 0 );
		if ( ! $sitemap_cache ) {
			$issues[] = 'Sitemap cache not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Yoast SEO indexation performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-indexation-performance',
			);
		}

		return null;
	}
}

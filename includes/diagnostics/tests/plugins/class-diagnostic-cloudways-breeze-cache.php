<?php
/**
 * Cloudways Breeze Cache Diagnostic
 *
 * Cloudways Breeze Cache needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1021.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudways Breeze Cache Diagnostic Class
 *
 * @since 1.1021.0000
 */
class Diagnostic_CloudwaysBreezeCache extends Diagnostic_Base {

	protected static $slug = 'cloudways-breeze-cache';
	protected static $title = 'Cloudways Breeze Cache';
	protected static $description = 'Cloudways Breeze Cache needs attention';
	protected static $family = 'performance';

	public static function check() {
		if ( ! get_option( 'bz_cache_enabled', '' ) && ! get_option( 'cloudways_breeze_active', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Cache enabled
		$cache_enabled = get_option( 'bz_cache_enabled', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Breeze cache not enabled';
		}

		// Check 2: Gzip compression enabled
		$gzip = get_option( 'bz_gzip_enabled', 0 );
		if ( ! $gzip ) {
			$issues[] = 'Gzip compression not enabled';
		}

		// Check 3: Browser caching enabled
		$browser_cache = get_option( 'bz_browser_cache_enabled', 0 );
		if ( ! $browser_cache ) {
			$issues[] = 'Browser caching not enabled';
		}

		// Check 4: Database optimization enabled
		$db_opt = get_option( 'bz_db_optimization_enabled', 0 );
		if ( ! $db_opt ) {
			$issues[] = 'Database optimization not enabled';
		}

		// Check 5: Minification enabled
		$minify = get_option( 'bz_minification_enabled', 0 );
		if ( ! $minify ) {
			$issues[] = 'Minification not enabled';
		}

		// Check 6: Cache expiration set
		$expiry = absint( get_option( 'bz_cache_expiration_hours', 0 ) );
		if ( $expiry <= 0 ) {
			$issues[] = 'Cache expiration not configured';
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
					'Found %d Breeze cache issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudways-breeze-cache',
			);
		}

		return null;
	}
}

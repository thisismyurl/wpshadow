<?php
/**
 * TablePress Cache Configuration Diagnostic
 *
 * TablePress not using transient caching.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.414.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Cache Configuration Diagnostic Class
 *
 * @since 1.414.0000
 */
class Diagnostic_TablepressCacheConfiguration extends Diagnostic_Base {

	protected static $slug = 'tablepress-cache-configuration';
	protected static $title = 'TablePress Cache Configuration';
	protected static $description = 'TablePress not using transient caching';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify transient caching enabled
		$cache_enabled = get_option( 'tablepress_use_cache', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'Transient caching not enabled';
		}

		// Check 2: Check cache expiration
		$cache_expiration = get_option( 'tablepress_cache_expiration', 0 );
		if ( $cache_expiration <= 0 ) {
			$issues[] = 'Cache expiration not configured';
		}

		// Check 3: Verify data table caching
		$data_cache = get_option( 'tablepress_cache_data', 0 );
		if ( ! $data_cache ) {
			$issues[] = 'Data table caching not enabled';
		}

		// Check 4: Check for automatic cache clearing
		$auto_clear = get_option( 'tablepress_cache_auto_clear', 0 );
		if ( ! $auto_clear ) {
			$issues[] = 'Automatic cache clearing not enabled';
		}

		// Check 5: Verify minified output
		$minify_output = get_option( 'tablepress_minify_output', 0 );
		if ( ! $minify_output ) {
			$issues[] = 'Minified output not enabled';
		}

		// Check 6: Check for lazy loading
		$lazy_loading = get_option( 'tablepress_lazy_loading', 0 );
		if ( ! $lazy_loading ) {
			$issues[] = 'Lazy loading not enabled';
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
					'Found %d TablePress cache issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-cache-configuration',
			);
		}

		return null;
	}
}

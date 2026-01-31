<?php
/**
 * Jupiter Theme Donut Cache Diagnostic
 *
 * Jupiter Theme Donut Cache needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1333.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jupiter Theme Donut Cache Diagnostic Class
 *
 * @since 1.1333.0000
 */
class Diagnostic_JupiterThemeDonutCache extends Diagnostic_Base {

	protected static $slug = 'jupiter-theme-donut-cache';
	protected static $title = 'Jupiter Theme Donut Cache';
	protected static $description = 'Jupiter Theme Donut Cache needs optimization';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MK_THEME_VERSION' ) && ! function_exists( 'mk_jupiter_version' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Donut cache enabled.
		$donut_cache = get_option( 'mk_donut_cache_enabled', '0' );
		if ( '0' === $donut_cache ) {
			$issues[] = 'donut caching disabled';
		}

		// Check 2: Cache fragments.
		$fragments = get_option( 'mk_cache_fragments', '1' );
		if ( '0' === $fragments ) {
			$issues[] = 'fragment caching disabled';
		}

		// Check 3: Cache timeout.
		$timeout = get_option( 'mk_cache_timeout', 3600 );
		if ( $timeout < 1800 ) {
			$issues[] = 'cache timeout too short';
		}

		// Check 4: Auto purge.
		$auto_purge = get_option( 'mk_cache_auto_purge', '1' );
		if ( '0' === $auto_purge ) {
			$issues[] = 'auto cache purge disabled';
		}

		// Check 5: Logged-in cache.
		$logged_in = get_option( 'mk_cache_logged_in', '0' );
		if ( '1' === $logged_in ) {
			$issues[] = 'caching logged-in users (security risk)';
		}

		// Check 6: Cache compression.
		$compression = get_option( 'mk_cache_compression', '1' );
		if ( '0' === $compression ) {
			$issues[] = 'cache compression disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 55 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Jupiter cache issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/jupiter-theme-donut-cache',
			);
		}

		return null;
	}
}

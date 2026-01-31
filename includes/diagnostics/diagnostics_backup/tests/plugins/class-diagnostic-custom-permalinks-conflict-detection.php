<?php
/**
 * Custom Permalinks Conflict Detection Diagnostic
 *
 * Custom Permalinks Conflict Detection issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1430.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Permalinks Conflict Detection Diagnostic Class
 *
 * @since 1.1430.0000
 */
class Diagnostic_CustomPermalinksConflictDetection extends Diagnostic_Base {

	protected static $slug = 'custom-permalinks-conflict-detection';
	protected static $title = 'Custom Permalinks Conflict Detection';
	protected static $description = 'Custom Permalinks Conflict Detection issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'custom_permalinks_init' ) && ! defined( 'CUSTOM_PERMALINKS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Conflict detection enabled.
		$conflict_check = get_option( 'custom_permalinks_check_conflicts', '1' );
		if ( '0' === $conflict_check ) {
			$issues[] = 'conflict detection disabled';
		}

		// Check 2: Auto-resolution.
		$auto_resolve = get_option( 'custom_permalinks_auto_resolve', '0' );
		if ( '0' === $auto_resolve ) {
			$issues[] = 'automatic conflict resolution disabled';
		}

		// Check 3: Redirect tracking.
		$redirect_track = get_option( 'custom_permalinks_track_redirects', '1' );
		if ( '0' === $redirect_track ) {
			$issues[] = 'redirect tracking disabled';
		}

		// Check 4: Conflict logging.
		$log_conflicts = get_option( 'custom_permalinks_log_conflicts', '1' );
		if ( '0' === $log_conflicts ) {
			$issues[] = 'conflict logging disabled';
		}

		// Check 5: Cache busting.
		$cache_bust = get_option( 'custom_permalinks_cache_bust', '1' );
		if ( '0' === $cache_bust ) {
			$issues[] = 'cache busting disabled';
		}

		// Check 6: Database optimization.
		$db_optimize = get_option( 'custom_permalinks_optimize_db', '1' );
		if ( '0' === $db_optimize ) {
			$issues[] = 'database optimization disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Custom Permalinks issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/custom-permalinks-conflict-detection',
			);
		}

		return null;
	}
}

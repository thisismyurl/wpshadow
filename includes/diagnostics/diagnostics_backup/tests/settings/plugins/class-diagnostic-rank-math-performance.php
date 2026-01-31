<?php
/**
 * Rank Math Performance Impact
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Rank_Math_Performance extends Diagnostic_Base {

	protected static $slug        = 'rank-math-performance';
	protected static $title       = 'Rank Math Performance Impact';
	protected static $description = 'Checks Rank Math database optimization';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_rank_math_performance';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		global $wpdb;

		// Check for Rank Math internal linking data bloat.
		$internal_links_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '_rank_math_internal_links%'"
		);

		if ( $internal_links_count > 5000 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( 'Rank Math has %d internal linking entries. Consider cleanup.', 'wpshadow' ),
					$internal_links_count
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rank-math-performance',
				'data'         => array(
					'internal_links_count' => (int) $internal_links_count,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

<?php
/**
 * First Input Delay Optimization
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

class Diagnostic_FID_Optimization extends Diagnostic_Base {

	protected static $slug        = 'fid-optimization';
	protected static $title       = 'First Input Delay Optimization';
	protected static $description = 'Detects heavy JavaScript blocking';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_fid_optimization';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wp_scripts;

		if ( ! $wp_scripts instanceof \WP_Scripts ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$blocking_scripts = 0;
		$heavy_scripts    = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
				$blocking_scripts++;
				if ( $blocking_scripts <= 5 ) {
					$heavy_scripts[] = $handle;
				}
			}
		}

		if ( $blocking_scripts > 10 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d blocking scripts detected. Defer/async for better FID.', 'wpshadow' ),
					$blocking_scripts
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-fid',
				'data'         => array(
					'blocking_count' => $blocking_scripts,
					'sample_scripts' => $heavy_scripts,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

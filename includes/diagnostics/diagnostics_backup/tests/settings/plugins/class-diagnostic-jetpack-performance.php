<?php
/**
 * Jetpack Performance Impact
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

class Diagnostic_Jetpack_Performance extends Diagnostic_Base {

	protected static $slug        = 'jetpack-performance';
	protected static $title       = 'Jetpack Performance Impact';
	protected static $description = 'Analyzes Jetpack CDN and caching';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_jetpack_performance';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'Jetpack' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$issues = array();

		// Check if Photon (CDN) is enabled.
		if ( ! \Jetpack::is_module_active( 'photon' ) ) {
			$issues[] = 'Jetpack Photon CDN not enabled';
		}

		// Check for site accelerator.
		if ( ! \Jetpack::is_module_active( 'photon-cdn' ) ) {
			$issues[] = 'Site Accelerator not enabled';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Jetpack performance features not fully configured.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/jetpack-performance',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

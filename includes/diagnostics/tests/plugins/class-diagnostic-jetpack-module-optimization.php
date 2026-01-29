<?php
/**
 * Jetpack Module Optimization
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

class Diagnostic_Jetpack_Module_Optimization extends Diagnostic_Base {

	protected static $slug        = 'jetpack-module-optimization';
	protected static $title       = 'Jetpack Module Optimization';
	protected static $description = 'Checks for unnecessary Jetpack modules';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_jetpack_modules';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'Jetpack' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$active_modules = \Jetpack::get_active_modules();
		$bloat_modules = array( 'stats', 'carousel', 'related-posts', 'likes', 'sharedaddy' );

		$enabled_bloat = array_intersect( $active_modules, $bloat_modules );

		if ( count( $enabled_bloat ) >= 3 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d potentially unnecessary Jetpack modules active. Disable for better performance.', 'wpshadow' ),
					count( $enabled_bloat )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/jetpack-modules',
				'data'         => array(
					'bloat_modules' => $enabled_bloat,
					'total_active'  => count( $active_modules ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}

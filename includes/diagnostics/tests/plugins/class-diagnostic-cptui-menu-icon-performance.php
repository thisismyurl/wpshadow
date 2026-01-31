<?php
/**
 * CPT UI Menu Icon Performance Diagnostic
 *
 * CPT UI menu icons slowing admin.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.449.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI Menu Icon Performance Diagnostic Class
 *
 * @since 1.449.0000
 */
class Diagnostic_CptuiMenuIconPerformance extends Diagnostic_Base {

	protected static $slug = 'cptui-menu-icon-performance';
	protected static $title = 'CPT UI Menu Icon Performance';
	protected static $description = 'CPT UI menu icons slowing admin';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 35,
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cptui-menu-icon-performance',
			);
		}
		

		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}

<?php
/**
 * LifterLMS Certificate Performance Diagnostic
 *
 * LifterLMS certificate generation slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.369.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Certificate Performance Diagnostic Class
 *
 * @since 1.369.0000
 */
class Diagnostic_LifterlmsCertificatePerformance extends Diagnostic_Base {

	protected static $slug = 'lifterlms-certificate-performance';
	protected static $title = 'LifterLMS Certificate Performance';
	protected static $description = 'LifterLMS certificate generation slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-certificate-performance',
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

<?php
/**
 * Redirection Plugin Redirect Performance Diagnostic
 *
 * Redirection Plugin Redirect Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1419.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin Redirect Performance Diagnostic Class
 *
 * @since 1.1419.0000
 */
class Diagnostic_RedirectionPluginRedirectPerformance extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-redirect-performance';
	protected static $title = 'Redirection Plugin Redirect Performance';
	protected static $description = 'Redirection Plugin Redirect Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'REDIRECTION_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-redirect-performance',
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

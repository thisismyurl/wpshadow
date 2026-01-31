<?php
/**
 * BuddyPress Activity Stream Diagnostic
 *
 * BuddyPress activity stream slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.513.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Activity Stream Diagnostic Class
 *
 * @since 1.513.0000
 */
class Diagnostic_BuddypressActivityStream extends Diagnostic_Base {

	protected static $slug = 'buddypress-activity-stream';
	protected static $title = 'BuddyPress Activity Stream';
	protected static $description = 'BuddyPress activity stream slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-activity-stream',
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

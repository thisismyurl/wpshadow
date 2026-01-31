<?php
/**
 * BuddyPress Member Directory Diagnostic
 *
 * BuddyPress member directory not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.237.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Member Directory Diagnostic Class
 *
 * @since 1.237.0000
 */
class Diagnostic_BuddypressMemberDirectoryOptimization extends Diagnostic_Base {

	protected static $slug = 'buddypress-member-directory-optimization';
	protected static $title = 'BuddyPress Member Directory';
	protected static $description = 'BuddyPress member directory not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'buddypress' ) ) {
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
				'severity'    => 40,
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-member-directory-optimization',
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

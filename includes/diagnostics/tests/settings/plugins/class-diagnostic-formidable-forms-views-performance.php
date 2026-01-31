<?php
/**
 * Formidable Forms Views Diagnostic
 *
 * Formidable Forms views not cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.265.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Views Diagnostic Class
 *
 * @since 1.265.0000
 */
class Diagnostic_FormidableFormsViewsPerformance extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-views-performance';
	protected static $title = 'Formidable Forms Views';
	protected static $description = 'Formidable Forms views not cached';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
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
				'severity'    => 35,
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-views-performance',
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

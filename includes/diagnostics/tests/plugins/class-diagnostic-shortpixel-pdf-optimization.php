<?php
/**
 * Shortpixel Pdf Optimization Diagnostic
 *
 * Shortpixel Pdf Optimization detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.749.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Pdf Optimization Diagnostic Class
 *
 * @since 1.749.0000
 */
class Diagnostic_ShortpixelPdfOptimization extends Diagnostic_Base {

	protected static $slug = 'shortpixel-pdf-optimization';
	protected static $title = 'Shortpixel Pdf Optimization';
	protected static $description = 'Shortpixel Pdf Optimization detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/shortpixel-pdf-optimization',
			);
		}
		
		return null;
	}
}

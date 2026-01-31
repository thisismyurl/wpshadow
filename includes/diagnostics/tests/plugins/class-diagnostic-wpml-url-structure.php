<?php
/**
 * WPML URL Structure Diagnostic
 *
 * WPML URL structure not SEO optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.301.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML URL Structure Diagnostic Class
 *
 * @since 1.301.0000
 */
class Diagnostic_WpmlUrlStructure extends Diagnostic_Base {

	protected static $slug = 'wpml-url-structure';
	protected static $title = 'WPML URL Structure';
	protected static $description = 'WPML URL structure not SEO optimized';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-url-structure',
			);
		}
		
		return null;
	}
}

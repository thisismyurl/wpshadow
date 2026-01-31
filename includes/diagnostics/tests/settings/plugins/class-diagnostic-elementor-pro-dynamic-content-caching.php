<?php
/**
 * Elementor Pro Dynamic Content Caching Diagnostic
 *
 * Elementor Pro Dynamic Content Caching issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.793.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Dynamic Content Caching Diagnostic Class
 *
 * @since 1.793.0000
 */
class Diagnostic_ElementorProDynamicContentCaching extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-dynamic-content-caching';
	protected static $title = 'Elementor Pro Dynamic Content Caching';
	protected static $description = 'Elementor Pro Dynamic Content Caching issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-dynamic-content-caching',
			);
		}
		
		return null;
	}
}

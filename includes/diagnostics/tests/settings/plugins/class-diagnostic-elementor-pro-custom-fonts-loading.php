<?php
/**
 * Elementor Pro Custom Fonts Loading Diagnostic
 *
 * Elementor Pro Custom Fonts Loading issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.794.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Custom Fonts Loading Diagnostic Class
 *
 * @since 1.794.0000
 */
class Diagnostic_ElementorProCustomFontsLoading extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-custom-fonts-loading';
	protected static $title = 'Elementor Pro Custom Fonts Loading';
	protected static $description = 'Elementor Pro Custom Fonts Loading issues found';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 55,
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-custom-fonts-loading',
			);
		}
		
		return null;
	}
}

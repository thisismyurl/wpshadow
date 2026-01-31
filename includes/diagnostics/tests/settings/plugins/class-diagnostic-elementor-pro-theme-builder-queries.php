<?php
/**
 * Elementor Pro Theme Builder Queries Diagnostic
 *
 * Elementor Pro Theme Builder Queries issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.792.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Theme Builder Queries Diagnostic Class
 *
 * @since 1.792.0000
 */
class Diagnostic_ElementorProThemeBuilderQueries extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-theme-builder-queries';
	protected static $title = 'Elementor Pro Theme Builder Queries';
	protected static $description = 'Elementor Pro Theme Builder Queries issues found';
	protected static $family = 'functionality';

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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-theme-builder-queries',
			);
		}
		
		return null;
	}
}

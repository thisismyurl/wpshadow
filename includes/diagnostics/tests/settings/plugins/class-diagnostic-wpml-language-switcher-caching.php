<?php
/**
 * Wpml Language Switcher Caching Diagnostic
 *
 * Wpml Language Switcher Caching misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1142.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Language Switcher Caching Diagnostic Class
 *
 * @since 1.1142.0000
 */
class Diagnostic_WpmlLanguageSwitcherCaching extends Diagnostic_Base {

	protected static $slug = 'wpml-language-switcher-caching';
	protected static $title = 'Wpml Language Switcher Caching';
	protected static $description = 'Wpml Language Switcher Caching misconfigured';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-language-switcher-caching',
			);
		}
		
		return null;
	}
}

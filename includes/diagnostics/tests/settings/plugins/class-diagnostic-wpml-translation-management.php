<?php
/**
 * WPML Translation Management Diagnostic
 *
 * WPML translation workflow inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.304.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Translation Management Diagnostic Class
 *
 * @since 1.304.0000
 */
class Diagnostic_WpmlTranslationManagement extends Diagnostic_Base {

	protected static $slug = 'wpml-translation-management';
	protected static $title = 'WPML Translation Management';
	protected static $description = 'WPML translation workflow inefficient';
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
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-translation-management',
			);
		}
		
		return null;
	}
}

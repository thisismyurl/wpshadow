<?php
/**
 * Ninja Tables Responsive Design Diagnostic
 *
 * Ninja Tables not mobile optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.480.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Responsive Design Diagnostic Class
 *
 * @since 1.480.0000
 */
class Diagnostic_NinjaTablesResponsiveDesign extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-responsive-design';
	protected static $title = 'Ninja Tables Responsive Design';
	protected static $description = 'Ninja Tables not mobile optimized';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'NINJA_TABLES_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-responsive-design',
			);
		}
		
		return null;
	}
}

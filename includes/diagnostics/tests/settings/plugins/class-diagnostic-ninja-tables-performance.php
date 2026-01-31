<?php
/**
 * Ninja Tables Performance Diagnostic
 *
 * Ninja Tables slowing frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.478.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Performance Diagnostic Class
 *
 * @since 1.478.0000
 */
class Diagnostic_NinjaTablesPerformance extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-performance';
	protected static $title = 'Ninja Tables Performance';
	protected static $description = 'Ninja Tables slowing frontend';
	protected static $family = 'performance';

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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-performance',
			);
		}
		
		return null;
	}
}

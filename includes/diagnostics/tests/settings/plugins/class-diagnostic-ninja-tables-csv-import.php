<?php
/**
 * Ninja Tables CSV Import Diagnostic
 *
 * Ninja Tables CSV imports insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.481.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables CSV Import Diagnostic Class
 *
 * @since 1.481.0000
 */
class Diagnostic_NinjaTablesCsvImport extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-csv-import';
	protected static $title = 'Ninja Tables CSV Import';
	protected static $description = 'Ninja Tables CSV imports insecure';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-csv-import',
			);
		}
		
		return null;
	}
}

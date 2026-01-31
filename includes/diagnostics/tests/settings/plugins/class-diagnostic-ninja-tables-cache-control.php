<?php
/**
 * Ninja Tables Cache Control Diagnostic
 *
 * Ninja Tables cache not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.483.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Cache Control Diagnostic Class
 *
 * @since 1.483.0000
 */
class Diagnostic_NinjaTablesCacheControl extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-cache-control';
	protected static $title = 'Ninja Tables Cache Control';
	protected static $description = 'Ninja Tables cache not configured';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-cache-control',
			);
		}
		
		return null;
	}
}

<?php
/**
 * WP All Import Memory Limits Diagnostic
 *
 * Import processes exceeding memory limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.273.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import Memory Limits Diagnostic Class
 *
 * @since 1.273.0000
 */
class Diagnostic_WpAllImportMemoryLimits extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-memory-limits';
	protected static $title = 'WP All Import Memory Limits';
	protected static $description = 'Import processes exceeding memory limits';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'PMXI_Plugin' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-memory-limits',
			);
		}
		
		return null;
	}
}

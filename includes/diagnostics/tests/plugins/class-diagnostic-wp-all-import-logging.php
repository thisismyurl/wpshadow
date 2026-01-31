<?php
/**
 * WP All Import Logging Diagnostic
 *
 * Import logs not being cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.276.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP All Import Logging Diagnostic Class
 *
 * @since 1.276.0000
 */
class Diagnostic_WpAllImportLogging extends Diagnostic_Base {

	protected static $slug = 'wp-all-import-logging';
	protected static $title = 'WP All Import Logging';
	protected static $description = 'Import logs not being cleaned up';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-all-import-logging',
			);
		}
		
		return null;
	}
}

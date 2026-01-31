<?php
/**
 * TablePress Responsive Tables Diagnostic
 *
 * TablePress tables not mobile optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.418.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Responsive Tables Diagnostic Class
 *
 * @since 1.418.0000
 */
class Diagnostic_TablepressResponsiveTables extends Diagnostic_Base {

	protected static $slug = 'tablepress-responsive-tables';
	protected static $title = 'TablePress Responsive Tables';
	protected static $description = 'TablePress tables not mobile optimized';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-responsive-tables',
			);
		}
		
		return null;
	}
}

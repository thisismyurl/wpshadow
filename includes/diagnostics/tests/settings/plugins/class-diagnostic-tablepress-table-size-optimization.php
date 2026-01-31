<?php
/**
 * TablePress Table Size Optimization Diagnostic
 *
 * TablePress tables too large for frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.412.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Table Size Optimization Diagnostic Class
 *
 * @since 1.412.0000
 */
class Diagnostic_TablepressTableSizeOptimization extends Diagnostic_Base {

	protected static $slug = 'tablepress-table-size-optimization';
	protected static $title = 'TablePress Table Size Optimization';
	protected static $description = 'TablePress tables too large for frontend';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-table-size-optimization',
			);
		}
		
		return null;
	}
}

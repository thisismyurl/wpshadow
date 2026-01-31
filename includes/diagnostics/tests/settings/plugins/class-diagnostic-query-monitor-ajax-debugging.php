<?php
/**
 * Query Monitor Ajax Debugging Diagnostic
 *
 * Query Monitor Ajax Debugging issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1041.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Monitor Ajax Debugging Diagnostic Class
 *
 * @since 1.1041.0000
 */
class Diagnostic_QueryMonitorAjaxDebugging extends Diagnostic_Base {

	protected static $slug = 'query-monitor-ajax-debugging';
	protected static $title = 'Query Monitor Ajax Debugging';
	protected static $description = 'Query Monitor Ajax Debugging issue detected';
	protected static $family = 'functionality';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/query-monitor-ajax-debugging',
			);
		}
		
		return null;
	}
}

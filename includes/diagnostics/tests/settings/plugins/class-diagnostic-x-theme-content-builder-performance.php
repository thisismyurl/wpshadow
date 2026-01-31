<?php
/**
 * X Theme Content Builder Performance Diagnostic
 *
 * X Theme Content Builder Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1329.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * X Theme Content Builder Performance Diagnostic Class
 *
 * @since 1.1329.0000
 */
class Diagnostic_XThemeContentBuilderPerformance extends Diagnostic_Base {

	protected static $slug = 'x-theme-content-builder-performance';
	protected static $title = 'X Theme Content Builder Performance';
	protected static $description = 'X Theme Content Builder Performance needs optimization';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/x-theme-content-builder-performance',
			);
		}
		
		return null;
	}
}

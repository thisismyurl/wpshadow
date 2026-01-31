<?php
/**
 * MonsterInsights Dashboard Widget Diagnostic
 *
 * MonsterInsights dashboard slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.429.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Dashboard Widget Diagnostic Class
 *
 * @since 1.429.0000
 */
class Diagnostic_MonsterinsightsDashboardWidgetPerformance extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-dashboard-widget-performance';
	protected static $title = 'MonsterInsights Dashboard Widget';
	protected static $description = 'MonsterInsights dashboard slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-dashboard-widget-performance',
			);
		}
		
		return null;
	}
}

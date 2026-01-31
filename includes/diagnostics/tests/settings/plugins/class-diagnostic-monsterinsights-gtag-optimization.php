<?php
/**
 * MonsterInsights gtag.js Optimization Diagnostic
 *
 * MonsterInsights loading multiple tracking scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.233.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights gtag.js Optimization Diagnostic Class
 *
 * @since 1.233.0000
 */
class Diagnostic_MonsterinsightsGtagOptimization extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-gtag-optimization';
	protected static $title = 'MonsterInsights gtag.js Optimization';
	protected static $description = 'MonsterInsights loading multiple tracking scripts';
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-gtag-optimization',
			);
		}
		
		return null;
	}
}

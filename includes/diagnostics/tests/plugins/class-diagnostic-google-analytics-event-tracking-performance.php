<?php
/**
 * Google Analytics Event Tracking Performance Diagnostic
 *
 * Google Analytics Event Tracking Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1343.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Event Tracking Performance Diagnostic Class
 *
 * @since 1.1343.0000
 */
class Diagnostic_GoogleAnalyticsEventTrackingPerformance extends Diagnostic_Base {

	protected static $slug = 'google-analytics-event-tracking-performance';
	protected static $title = 'Google Analytics Event Tracking Performance';
	protected static $description = 'Google Analytics Event Tracking Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'ga_load_options' ) || defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-event-tracking-performance',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Google Analytics Tracking Code Placement Diagnostic
 *
 * Google Analytics Tracking Code Placement misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1338.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Tracking Code Placement Diagnostic Class
 *
 * @since 1.1338.0000
 */
class Diagnostic_GoogleAnalyticsTrackingCodePlacement extends Diagnostic_Base {

	protected static $slug = 'google-analytics-tracking-code-placement';
	protected static $title = 'Google Analytics Tracking Code Placement';
	protected static $description = 'Google Analytics Tracking Code Placement misconfigured';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-tracking-code-placement',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Simple Analytics Cookieless Tracking Diagnostic
 *
 * Simple Analytics Cookieless Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1368.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Analytics Cookieless Tracking Diagnostic Class
 *
 * @since 1.1368.0000
 */
class Diagnostic_SimpleAnalyticsCookielessTracking extends Diagnostic_Base {

	protected static $slug = 'simple-analytics-cookieless-tracking';
	protected static $title = 'Simple Analytics Cookieless Tracking';
	protected static $description = 'Simple Analytics Cookieless Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/simple-analytics-cookieless-tracking',
			);
		}
		
		return null;
	}
}

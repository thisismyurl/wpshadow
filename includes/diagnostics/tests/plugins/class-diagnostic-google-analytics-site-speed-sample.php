<?php
/**
 * Google Analytics Site Speed Sample Diagnostic
 *
 * Google Analytics Site Speed Sample misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1341.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Site Speed Sample Diagnostic Class
 *
 * @since 1.1341.0000
 */
class Diagnostic_GoogleAnalyticsSiteSpeedSample extends Diagnostic_Base {

	protected static $slug = 'google-analytics-site-speed-sample';
	protected static $title = 'Google Analytics Site Speed Sample';
	protected static $description = 'Google Analytics Site Speed Sample misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'ga_load_options' ) || defined( 'MONSTERINSIGHTS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-site-speed-sample',
			);
		}
		
		return null;
	}
}

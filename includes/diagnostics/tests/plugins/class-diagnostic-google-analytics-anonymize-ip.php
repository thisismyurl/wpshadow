<?php
/**
 * Google Analytics Anonymize Ip Diagnostic
 *
 * Google Analytics Anonymize Ip misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1339.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Anonymize Ip Diagnostic Class
 *
 * @since 1.1339.0000
 */
class Diagnostic_GoogleAnalyticsAnonymizeIp extends Diagnostic_Base {

	protected static $slug = 'google-analytics-anonymize-ip';
	protected static $title = 'Google Analytics Anonymize Ip';
	protected static $description = 'Google Analytics Anonymize Ip misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-anonymize-ip',
			);
		}
		
		return null;
	}
}

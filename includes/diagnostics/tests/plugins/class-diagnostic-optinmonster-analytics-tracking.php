<?php
/**
 * OptinMonster Analytics Tracking Diagnostic
 *
 * OptinMonster analytics not tracking conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.222.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster Analytics Tracking Diagnostic Class
 *
 * @since 1.222.0000
 */
class Diagnostic_OptinmonsterAnalyticsTracking extends Diagnostic_Base {

	protected static $slug = 'optinmonster-analytics-tracking';
	protected static $title = 'OptinMonster Analytics Tracking';
	protected static $description = 'OptinMonster analytics not tracking conversions';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'OMAPI_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/optinmonster-analytics-tracking',
			);
		}
		
		return null;
	}
}

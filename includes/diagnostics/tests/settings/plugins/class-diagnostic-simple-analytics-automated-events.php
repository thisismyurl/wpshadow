<?php
/**
 * Simple Analytics Automated Events Diagnostic
 *
 * Simple Analytics Automated Events misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1370.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Analytics Automated Events Diagnostic Class
 *
 * @since 1.1370.0000
 */
class Diagnostic_SimpleAnalyticsAutomatedEvents extends Diagnostic_Base {

	protected static $slug = 'simple-analytics-automated-events';
	protected static $title = 'Simple Analytics Automated Events';
	protected static $description = 'Simple Analytics Automated Events misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/simple-analytics-automated-events',
			);
		}
		
		return null;
	}
}

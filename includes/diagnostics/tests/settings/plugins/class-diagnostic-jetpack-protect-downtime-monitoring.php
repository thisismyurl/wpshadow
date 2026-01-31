<?php
/**
 * Jetpack Protect Downtime Monitoring Diagnostic
 *
 * Jetpack Protect Downtime Monitoring misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.875.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Downtime Monitoring Diagnostic Class
 *
 * @since 1.875.0000
 */
class Diagnostic_JetpackProtectDowntimeMonitoring extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-downtime-monitoring';
	protected static $title = 'Jetpack Protect Downtime Monitoring';
	protected static $description = 'Jetpack Protect Downtime Monitoring misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-downtime-monitoring',
			);
		}
		
		return null;
	}
}

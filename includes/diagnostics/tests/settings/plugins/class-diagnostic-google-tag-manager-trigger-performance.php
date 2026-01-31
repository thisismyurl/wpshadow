<?php
/**
 * Google Tag Manager Trigger Performance Diagnostic
 *
 * Google Tag Manager Trigger Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1346.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Trigger Performance Diagnostic Class
 *
 * @since 1.1346.0000
 */
class Diagnostic_GoogleTagManagerTriggerPerformance extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-trigger-performance';
	protected static $title = 'Google Tag Manager Trigger Performance';
	protected static $description = 'Google Tag Manager Trigger Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-trigger-performance',
			);
		}
		
		return null;
	}
}

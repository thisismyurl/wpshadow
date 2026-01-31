<?php
/**
 * Mixpanel Event Tracking Accuracy Diagnostic
 *
 * Mixpanel Event Tracking Accuracy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1383.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mixpanel Event Tracking Accuracy Diagnostic Class
 *
 * @since 1.1383.0000
 */
class Diagnostic_MixpanelEventTrackingAccuracy extends Diagnostic_Base {

	protected static $slug = 'mixpanel-event-tracking-accuracy';
	protected static $title = 'Mixpanel Event Tracking Accuracy';
	protected static $description = 'Mixpanel Event Tracking Accuracy misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/mixpanel-event-tracking-accuracy',
			);
		}
		
		return null;
	}
}

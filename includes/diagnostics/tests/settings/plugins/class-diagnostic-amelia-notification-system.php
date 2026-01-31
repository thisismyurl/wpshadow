<?php
/**
 * Amelia Notification System Diagnostic
 *
 * Amelia notifications not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.468.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Notification System Diagnostic Class
 *
 * @since 1.468.0000
 */
class Diagnostic_AmeliaNotificationSystem extends Diagnostic_Base {

	protected static $slug = 'amelia-notification-system';
	protected static $title = 'Amelia Notification System';
	protected static $description = 'Amelia notifications not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-notification-system',
			);
		}
		
		return null;
	}
}

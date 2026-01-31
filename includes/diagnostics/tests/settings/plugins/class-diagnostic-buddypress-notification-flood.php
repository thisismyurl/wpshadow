<?php
/**
 * BuddyPress Notification Flood Diagnostic
 *
 * BuddyPress notifications flooding users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.516.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Notification Flood Diagnostic Class
 *
 * @since 1.516.0000
 */
class Diagnostic_BuddypressNotificationFlood extends Diagnostic_Base {

	protected static $slug = 'buddypress-notification-flood';
	protected static $title = 'BuddyPress Notification Flood';
	protected static $description = 'BuddyPress notifications flooding users';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-notification-flood',
			);
		}
		
		return null;
	}
}

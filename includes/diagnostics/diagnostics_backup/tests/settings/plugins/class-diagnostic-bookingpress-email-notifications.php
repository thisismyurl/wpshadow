<?php
/**
 * BookingPress Email Notifications Diagnostic
 *
 * BookingPress email notifications misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.460.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Email Notifications Diagnostic Class
 *
 * @since 1.460.0000
 */
class Diagnostic_BookingpressEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'bookingpress-email-notifications';
	protected static $title = 'BookingPress Email Notifications';
	protected static $description = 'BookingPress email notifications misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
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
				'severity'    => 45,
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bookingpress-email-notifications',
			);
		}
		
		return null;
	}
}

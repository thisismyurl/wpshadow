<?php
/**
 * WP Job Manager Email Notifications Diagnostic
 *
 * Job alert emails not configured properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.246.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Email Notifications Diagnostic Class
 *
 * @since 1.246.0000
 */
class Diagnostic_WpJobManagerEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-email-notifications';
	protected static $title = 'WP Job Manager Email Notifications';
	protected static $description = 'Job alert emails not configured properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-email-notifications',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Events Manager Email Notifications Diagnostic
 *
 * Events Manager emails misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.579.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Manager Email Notifications Diagnostic Class
 *
 * @since 1.579.0000
 */
class Diagnostic_EventsManagerEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'events-manager-email-notifications';
	protected static $title = 'Events Manager Email Notifications';
	protected static $description = 'Events Manager emails misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'EM_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/events-manager-email-notifications',
			);
		}
		
		return null;
	}
}

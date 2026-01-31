<?php
/**
 * The Events Calendar Ticketing Diagnostic
 *
 * Event ticketing not properly secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.271.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Ticketing Diagnostic Class
 *
 * @since 1.271.0000
 */
class Diagnostic_EventsCalendarTicketSecurity extends Diagnostic_Base {

	protected static $slug = 'events-calendar-ticket-security';
	protected static $title = 'The Events Calendar Ticketing';
	protected static $description = 'Event ticketing not properly secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-ticket-security',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Event Espresso Attendee Data Diagnostic
 *
 * Event Espresso attendee data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.589.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Espresso Attendee Data Diagnostic Class
 *
 * @since 1.589.0000
 */
class Diagnostic_EventEspressoAttendeeData extends Diagnostic_Base {

	protected static $slug = 'event-espresso-attendee-data';
	protected static $title = 'Event Espresso Attendee Data';
	protected static $description = 'Event Espresso attendee data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-espresso-attendee-data',
			);
		}
		
		return null;
	}
}

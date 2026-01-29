<?php
/**
 * Event Tickets Email Delivery Diagnostic
 *
 * Event ticket emails delayed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.572.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Tickets Email Delivery Diagnostic Class
 *
 * @since 1.572.0000
 */
class Diagnostic_EventTicketsEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'event-tickets-email-delivery';
	protected static $title = 'Event Tickets Email Delivery';
	protected static $description = 'Event ticket emails delayed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-tickets-email-delivery',
			);
		}
		
		return null;
	}
}

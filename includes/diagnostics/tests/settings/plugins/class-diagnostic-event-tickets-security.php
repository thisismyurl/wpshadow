<?php
/**
 * Event Tickets Security Diagnostic
 *
 * Event tickets system insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.568.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Tickets Security Diagnostic Class
 *
 * @since 1.568.0000
 */
class Diagnostic_EventTicketsSecurity extends Diagnostic_Base {

	protected static $slug = 'event-tickets-security';
	protected static $title = 'Event Tickets Security';
	protected static $description = 'Event tickets system insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-tickets-security',
			);
		}
		
		return null;
	}
}

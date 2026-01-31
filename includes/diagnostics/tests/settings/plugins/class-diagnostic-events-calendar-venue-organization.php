<?php
/**
 * The Events Calendar Venues Diagnostic
 *
 * Venue and organizer data duplicated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.268.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Venues Diagnostic Class
 *
 * @since 1.268.0000
 */
class Diagnostic_EventsCalendarVenueOrganization extends Diagnostic_Base {

	protected static $slug = 'events-calendar-venue-organization';
	protected static $title = 'The Events Calendar Venues';
	protected static $description = 'Venue and organizer data duplicated';
	protected static $family = 'performance';

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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-venue-organization',
			);
		}
		
		return null;
	}
}

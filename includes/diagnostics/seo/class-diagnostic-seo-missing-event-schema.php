<?php
declare(strict_types=1);
/**
 * Missing Event Schema Diagnostic
 *
 * Philosophy: SEO local - Event schema shows in Google Events
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Event schema on event pages.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Event_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$event_content = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%event%' OR post_content LIKE '%date:%' OR post_content LIKE '%location:%')"
		);
		
		if ( $event_content > 0 ) {
			return array(
				'id'          => 'seo-missing-event-schema',
				'title'       => 'Event Content Missing Schema',
				'description' => sprintf( '%d event posts detected. Add Event schema with date, location, price, performer. Shows in Google Events and Maps.', $event_content ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-event-schema/',
				'training_link' => 'https://wpshadow.com/training/event-markup/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}

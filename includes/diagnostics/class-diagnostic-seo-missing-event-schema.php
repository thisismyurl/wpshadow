<?php declare(strict_types=1);
/**
 * Missing Event Schema Diagnostic
 *
 * Philosophy: SEO local - Event schema shows in Google Events
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for Event schema on event pages.
 */
class Diagnostic_SEO_Missing_Event_Schema {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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

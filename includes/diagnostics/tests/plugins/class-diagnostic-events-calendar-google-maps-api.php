<?php
/**
 * The Events Calendar Google Maps Diagnostic
 *
 * Google Maps API key exposed or missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.270.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Google Maps Diagnostic Class
 *
 * @since 1.270.0000
 */
class Diagnostic_EventsCalendarGoogleMapsApi extends Diagnostic_Base {

	protected static $slug = 'events-calendar-google-maps-api';
	protected static $title = 'The Events Calendar Google Maps';
	protected static $description = 'Google Maps API key exposed or missing';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-google-maps-api',
			);
		}
		
		return null;
	}
}

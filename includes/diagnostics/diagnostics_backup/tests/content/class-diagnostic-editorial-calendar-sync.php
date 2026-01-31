<?php
/**
 * Editorial Calendar Sync
 *
 * Checks if an editorial calendar is integrated and syncing properly
 * with the WordPress publishing schedule.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6029.1103
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editorial Calendar Sync Diagnostic Class
 *
 * Verifies editorial calendar integration and synchronization.
 *
 * @since 1.6029.1103
 */
class Diagnostic_Editorial_Calendar_Sync extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'editorial-calendar-sync';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Editorial Calendar Sync';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if editorial calendar is integrated and syncing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1103
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_editorial_calendar_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$calendar_status = self::check_calendar_integration();

		if ( $calendar_status['has_calendar'] ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No editorial calendar plugin detected. Content planning may lack visual organization.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/editorial-calendar',
			'meta'         => array(
				'has_calendar'   => false,
				'scheduled_count' => $calendar_status['scheduled_count'],
			),
			'details'      => array(
				__( 'No editorial calendar plugin installed', 'wpshadow' ),
				__( 'Visual calendar improves content planning', 'wpshadow' ),
				__( 'Helps coordinate multi-author publishing', 'wpshadow' ),
			),
			'recommendation' => __( 'Install Editorial Calendar, CoSchedule, or similar plugin for better content planning.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Check calendar integration.
	 *
	 * @since  1.6029.1103
	 * @return array Calendar status.
	 */
	private static function check_calendar_integration() {
		// Check for popular editorial calendar plugins.
		$calendar_plugins = array(
			'editorial-calendar/edcal.php',
			'coschedule-by-todaymade/coschedule.php',
			'publishpress/publishpress.php',
			'edit-flow/edit_flow.php',
		);

		$has_calendar = false;
		foreach ( $calendar_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_calendar = true;
				break;
			}
		}

		// Get scheduled content count.
		$scheduled_count = wp_count_posts()->future ?? 0;

		return array(
			'has_calendar'    => $has_calendar,
			'scheduled_count' => $scheduled_count,
		);
	}
}

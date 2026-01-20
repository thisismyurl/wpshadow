<?php
/**
 * RSS Feed Links Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Interface;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove RSS feed links from head.
 */
class Treatment_RSS_Feeds implements Treatment_Interface {

	public static function get_finding_id() {
		return 'rss-feeds';
	}

	public static function can_apply() {
		return current_user_can( 'manage_options' );
	}

	public static function apply() {
		update_option( 'wpshadow_rss_feeds_disabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'performance' );
		
		return array(
			'success' => true,
			'message' => __( 'RSS feed links removed from page head. Your RSS feeds still work at /feed/, but are not auto-discovered.', 'wpshadow' ),
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_rss_feeds_disabled' );
		KPI_Tracker::log_fix_undone( self::get_finding_id() );
		
		return array(
			'success' => true,
			'message' => __( 'RSS feed links restored in page head.', 'wpshadow' ),
		);
	}
}

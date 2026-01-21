<?php
/**
 * Theme Update Noise Treatment
 *
 * Suppresses update notices for inactive themes to reduce dashboard noise.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Admin\Update_Notification_Manager;
use WPShadow\Core\KPI_Tracker;
use WPShadow\Core\Treatment_Base;

class Treatment_Theme_Update_Noise extends Treatment_Base {
	public static function get_finding_id() {
		return 'theme-update-noise';
	}

	public static function can_apply() {
		return count( Update_Notification_Manager::get_inactive_theme_slugs() ) > 0;
	}

	public static function apply() {
		$inactive = Update_Notification_Manager::get_inactive_theme_slugs();
		if ( empty( $inactive ) ) {
			return array(
				'success' => false,
				'message' => __( 'No inactive themes detected.', 'wpshadow' ),
			);
		}

		$added = Update_Notification_Manager::suppress_themes( $inactive );
		if ( $added > 0 ) {
			KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		}

		$count = count( $inactive );
		return array(
			'success' => true,
			'message' => sprintf(
				_n( 'Hidden update notices for %d inactive theme. Delete unused themes from Appearance > Themes when ready.', 'Hidden update notices for %d inactive themes. Delete unused themes from Appearance > Themes when ready.', $count, 'wpshadow' ),
				$count
			),
		);
	}

	public static function undo() {
		Update_Notification_Manager::clear_theme_suppression();
		return array(
			'success' => true,
			'message' => __( 'Theme update suppression cleared. Notifications are visible again.', 'wpshadow' ),
		);
	}
}

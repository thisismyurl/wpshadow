<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Nav_ARIA extends Treatment_Base {

	public static function get_finding_id(): string {
		return 'nav-aria';
	}

	public static function apply(): array {
		update_option( 'wpshadow_nav_accessibility_enabled', true, false );

		KPI_Tracker::log_fix_applied( 'nav-aria', 'accessibility' );

		return array(
			'success' => true,
			'message' => __( 'Navigation accessibility enabled. ARIA current-page attributes will be added to active menu items.', 'wpshadow' ),
		);
	}

	public static function undo(): array {
		delete_option( 'wpshadow_nav_accessibility_enabled' );

		return array(
			'success' => true,
			'message' => __( 'Navigation accessibility disabled. ARIA attributes removed.', 'wpshadow' ),
		);
	}
}

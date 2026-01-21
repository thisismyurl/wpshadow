<?php
declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Asset_Versions extends Treatment_Base {

	public static function get_finding_id(): string {
		return 'asset-versions';
	}

	public static function apply(): array {
		update_option( 'wpshadow_asset_version_removal_enabled', true, false );

		KPI_Tracker::log_fix_applied( 'asset-versions', 'performance' );

		return array(
			'success' => true,
			'message' => __( 'Asset version removal enabled. Version query strings will be stripped from CSS and JS files.', 'wpshadow' ),
		);
	}

	public static function undo(): array {
		delete_option( 'wpshadow_asset_version_removal_enabled' );

		return array(
			'success' => true,
			'message' => __( 'Asset version removal disabled. Original version strings restored.', 'wpshadow' ),
		);
	}
}

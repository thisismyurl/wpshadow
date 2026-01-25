<?php
/**
 * Strip Resource Hints Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

class Treatment_Strip_Resource_Hints extends Treatment_Base {
	public static function get_finding_id() {
		return 'third-party-resource-hints-present';
	}

	public static function apply() {
		update_option( 'wpshadow_strip_resource_hints', true );
		if ( class_exists( '\\WPShadow\\Core\\KPI_Tracker' ) ) {
			KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		}
		return array(
			'success' => true,
			'message' => 'All resource hints (dns-prefetch, preconnect, preload, prefetch) will be stripped.',
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_strip_resource_hints' );
		return array(
			'success' => true,
			'message' => 'Resource hints stripping disabled.',
		);
	}
}

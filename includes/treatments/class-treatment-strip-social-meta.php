<?php
/**
 * Strip Social Meta Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

class Treatment_Strip_Social_Meta extends Treatment_Base {
	public static function get_finding_id() {
		return 'social-meta-present';
	}

	public static function apply() {
		update_option( 'wpshadow_strip_social_meta', true );
		if ( class_exists( '\\WPShadow\\Core\\KPI_Tracker' ) ) {
			KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		}
		return array(
			'success' => true,
			'message' => 'OpenGraph and Twitter meta tags will be stripped from the output.',
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_strip_social_meta' );
		return array(
			'success' => true,
			'message' => 'Social meta stripping disabled.',
		);
	}
}

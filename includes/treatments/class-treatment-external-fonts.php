<?php
/**
 * External Fonts Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to block Google-hosted fonts for privacy/performance.
 */
class Treatment_External_Fonts extends Treatment_Base {
	public static function get_finding_id() {
		return 'external-fonts-loading';
	}
	
	public static function apply() {
		update_option( 'wpshadow_block_external_fonts', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Blocking Google-hosted fonts; system font stack will be used where applicable.',
		);
	}
	
	public static function undo() {
		delete_option( 'wpshadow_block_external_fonts' );
		return array(
			'success' => true,
			'message' => 'External font blocking disabled.',
		);
	}
}

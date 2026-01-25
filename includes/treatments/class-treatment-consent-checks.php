<?php
/**
 * Consent Banner Treatment
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

/**
 * Treatment to enable a simple consent banner.
 */
class Treatment_Consent_Checks extends Treatment_Base {
	public static function get_finding_id() {
		return 'consent-missing';
	}

	public static function apply() {
		update_option( 'wpshadow_consent_enabled', true );
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		return array(
			'success' => true,
			'message' => 'Consent banner enabled site-wide.',
		);
	}

	public static function undo() {
		delete_option( 'wpshadow_consent_enabled' );
		return array(
			'success' => true,
			'message' => 'Consent banner disabled.',
		);
	}
}
